<?php

namespace App\Models;


use App\DTO\AccountDTO;
use PDO;
use Symphograph\Bicycle\Api\CurlAPI;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Auth\Discord\DiscordUser;
use Symphograph\Bicycle\Auth\Mailru\MailruUser;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\Auth\Vkontakte\VkUser;
use Symphograph\Bicycle\DTO\SocialAccountDTO;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Env\Services\Service;
use Symphograph\Bicycle\Files\FileIMG;
use Symphograph\Bicycle\Helpers\Date;
use Symphograph\Bicycle\PDO\DB;
use Symphograph\Bicycle\Errors\{AccountErr, AppErr, Auth\AuthErr, CurlErr, MyErrors};
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Token\AccessTokenData;
use Throwable;

class Account extends AccountDTO
{
    use ModelTrait;
    public const array authTypes = [
        'default',
        'telegram',
        'mailru',
        'server',
        'vkontakte',
        'yandex',
        'discord',
        'email'
    ];

    public ?string          $externalAvaUrl;
    public ?string          $label;
    public ?string          $nickName;
    public ?Avatar          $Avatar;
    public ?TeleUser        $TeleUser;
    public ?MailruUser      $MailruUser;
    public ?DiscordUser     $DiscordUser;
    public ?VkUser          $VkUser;
    public SocialAccountDTO $socialProfile;
    public FileIMG          $AvaIMG;
    public string           $contactValue;


    public static function create(string $authType): self
    {
        try {
            $Account = new self();
            $Account->authType = $authType;
            $datetime = date('Y-m-d H:i:s');
            $Account->createdAt = $datetime;
            $Account->visitedAt = $datetime;
            $Account->putToDB();
            $Account = self::byId(DB::lastId())
            or throw new AppErr('error on save Account', 'Ошибка при сохранении аккаунта');
        } catch (MyErrors $err) {
            throw new AuthErr($err->getMessage(), 'Не удалось создать аккаунт');
        }
        return $Account;
    }

    public static function byContact(Contact $contact): self|false
    {
        $socialProfile = match ($contact->type){
            'telegram' => TeleUser::byContact($contact->value),
            'discord' => DiscordUser::byContact($contact->value),
            'vkontakte' => VkUser::byContact($contact->value),
            'mailru' => MailruUser::byContact($contact->value),
            default => false
        };
        if(!$socialProfile){
            return false;
        }
        return self::byIdAndInit($socialProfile->accountId);
    }

    public function initData(): static
    {
        //if($this->authType === 'default') return;
        $this->initSocialProfile();
        $this->initAvatar();
        $this->datesToISO_8601();
        return $this;
    }

    private function datesToISO_8601(): void
    {
        $this->createdAt = Date::dateFormatFeel($this->createdAt, 'c');
        $this->visitedAt = Date::dateFormatFeel($this->visitedAt, 'c');
    }

    private function initAvatar(): void
    {
        if($this->authType === 'default'){
            $this->Avatar = new Avatar();
            return;
        }
        if(empty($this->avaFileName)){
            self::loadAvatar();
            return;
        }
        $Avatar = Avatar::byAvaFileName($this->avaFileName);
        if(!$Avatar){
            $Avatar = new Avatar();
        }
        $this->Avatar = $Avatar;
    }

    private function loadAvatar(): void
    {
        $Avatar = Avatar::byExternalUrl($this->externalAvaUrl);
        if(!$Avatar) {
            return;
        }


        $this->avaFileName = $Avatar->fileName;
        self::putToDB();
        $this->Avatar = $Avatar;
    }

    public static function byJwt(string $jwt): self|bool
    {
        $AccessTokenData = new AccessTokenData($jwt);
        return Account::byId($AccessTokenData->accountId);
    }

    public function initSocialProfile(): void
    {
        match ($this->authType){
            'default' => self::initDefault(),
            'telegram' => self::initTeleUser(),
            'mailru' => self::initMailruUser(),
            'discord' => self::initDiscordUser(),
            'vkontakte' => self::initVkUser(),
            default => null
        };
    }

    private function initDefault(): void
    {
        $this->nickName = 'Не авторизован';
        $this->label = 'default';
    }

    private function initTeleUser(): void
    {
        $TeleUser = TeleUser::byAccountId($this->id)
            or throw new AccountErr('Account does not exist', 'Аккаунт не найден');

        $this->TeleUser = $TeleUser;
        $this->socialProfile = $TeleUser;
        $this->externalAvaUrl = $TeleUser->photo_url;
        $this->label = 'Телеграм';
        $this->nickName = self::nickByNames($TeleUser->first_name, $TeleUser->last_name);
        $this->contactValue = $TeleUser->username;
    }

    private static function nickByNames($firstName, $lastName): string
    {
        $nickName = ($firstName ?? '') . ' ' . ($lastName);
        return trim($nickName);
    }

    private function initMailruUser(): void
    {
        $MailruUser = MailruUser::byAccountId($this->id)
            or throw new AccountErr('Account does not exist', 'Аккаунт не найден');

        $this->MailruUser = $MailruUser;
        $this->externalAvaUrl = $MailruUser->image;
        $this->label = 'mail.ru';
        $this->nickName = $MailruUser->getNickName();
        $this->contactValue = $MailruUser->email;
    }

    private function initDiscordUser(): void
    {
        $DiscordUser = DiscordUser::byAccountId($this->id)
            or throw new AccountErr('Account does not exist', 'Аккаунт не найден');

        $this->DiscordUser = $DiscordUser;
        $this->externalAvaUrl = "https://cdn.discordapp.com/avatars/$DiscordUser->id/$DiscordUser->avatar.png";
        $this->label = 'discord';
        $this->nickName = $DiscordUser->username;
        $this->contactValue = $DiscordUser->username;
    }

    private function initVkUser(): void
    {
        $VkUser = VkUser::byAccountId($this->id)
        or throw new AccountErr('Account does not exist', 'Аккаунт не найден');

        $this->VkUser = $VkUser;
        $this->externalAvaUrl = $VkUser->photo_rec;
        $this->label = 'vkontakte';
        $this->nickName = self::nickByNames($VkUser->first_name, $VkUser->last_name);
        $this->contactValue = $VkUser->domain;
    }

    public function getPowers(): array
    {
        if($this->authType === 'default') {
            return [];
        }

        $powerServiceName = Env::getPowerServiceName();
        $powerService = Service::byName($powerServiceName);
        $apiUrl = $powerService->getUrl();
        $url = "$apiUrl/epoint/powers.php";


        $this->initSocialProfile();
        $contact = new Contact();
        $contact->type = $this->authType;
        $contact->value = $this->contactValue;

        $params = ['method' => 'getByContact', 'contact' => $contact->getAllProps()];

        $curl = new CurlAPI($url, $params);

        try {
            $response = $curl->post();
        } catch (Throwable $err) {
            throw new CurlErr('Curl: ' . $err->getMessage(), 'Ошибка при получении доступа');
        }
        $powers = $response->data->powers ?? [];
        $persId = $response->data->persId ?? null;
        return compact('powers', 'persId');
    }

}