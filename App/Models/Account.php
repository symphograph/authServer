<?php

namespace App\Models;


use App\DTO\AccountDTO;
use PDO;
use Symphograph\Bicycle\Auth\Discord\DiscordUser;
use Symphograph\Bicycle\Auth\Mailru\MailruUser;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\Auth\Vkontakte\VkUser;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Errors\{AccountErr, AppErr, AuthErr, MyErrors};
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Token\AccessTokenData;

class Account extends AccountDTO
{
    use ModelTrait;
    public const authTypes = [
        'default',
        'telegram',
        'mailru',
        'server',
        'vkontakte',
        'yandex',
        'discord',
        'email'
    ];

    public ?string      $externalAvaUrl;
    public ?string      $label;
    public ?string      $nickName;
    public ?Avatar      $Avatar;
    public ?TeleUser    $TeleUser;
    public ?MailruUser  $MailruUser;
    public ?DiscordUser $DiscordUser;
    public ?VkUser      $VkUser;


    public static function create(int $userId, string $authType): self
    {
        try {
            $Account = new self();
            $Account->id = DB::createNewID('accounts', 'id');
            $Account->userId = $userId;
            $Account->authType = $authType;
            $datetime = date('Y-m-d H:i:s');
            $Account->createdAt = $datetime;
            $Account->visitedAt = $datetime;
            $Account->putToDB();
            $Account = self::byId($Account->id) or throw new AppErr('Account');
        } catch (MyErrors) {
            throw new AuthErr('Account was not created', 'Не удалось создать аккаунт');
        }
        return $Account;
    }

    public function initData(): void
    {
        //if($this->authType === 'default') return;
        self::initSocialProfile();
        self::initAvatar();
    }

    public function initAvatar(): void
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
            self::loadAvatar();
            return;
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
            'default' => true,
            'telegram' => self::initTeleUser(),
            'mailru' => self::initMailruUser(),
            'discord' => self::initDiscordUser(),
            'vkontakte' => self::initVkUser(),
            default => null
        };
    }

    private function initTeleUser(): void
    {
        $TeleUser = TeleUser::byAccountId($this->id)
            or throw new AccountErr('Account does not exist', 'Аккаунт не найден');

        $this->TeleUser = $TeleUser;
        $this->externalAvaUrl = $TeleUser->photo_url;
        $this->label = 'Телеграм';
        $this->nickName = self::nickByNames($TeleUser->first_name, $TeleUser->last_name);
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
    }

    private function initDiscordUser(): void
    {
        $DiscordUser = DiscordUser::byAccountId($this->id)
            or throw new AccountErr('Account does not exist', 'Аккаунт не найден');

        $this->DiscordUser = $DiscordUser;
        $this->externalAvaUrl = "https://cdn.discordapp.com/avatars/$DiscordUser->id/$DiscordUser->avatar.png";
        $this->label = 'discord';
        $this->nickName = $DiscordUser->username;
    }

    private function initVkUser(): void
    {
        $VkUser = VkUser::byAccountId($this->id)
        or throw new AccountErr('Account does not exist', 'Аккаунт не найден');

        $this->VkUser = $VkUser;
        $this->externalAvaUrl = $VkUser->photo_rec;
        $this->label = 'vkontakte';
        $this->nickName = self::nickByNames($VkUser->first_name, $VkUser->last_name);
    }

    /**
     * @return self[]
     */
    public static function getListByUser(int $userId): array
    {
        $qwe = qwe("select * from accounts where userId = :userId", ['userId' => $userId]);
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class) ?? [];
    }

    /**
     * @param int $deviceId
     * @return self[]
     */
    public static function getListByDevice(int $deviceId): array
    {
        $qwe = qwe("
            select accounts.* from accounts 
            inner join deviceAccount dA 
            on accounts.id = dA.accountId
            and deviceId = :deviceId",
        ['deviceId' => $deviceId]
        );
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class) ?? [];
    }

}