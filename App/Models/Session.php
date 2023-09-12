<?php

namespace App\Models;

use App\DTO\SessionDTO;
use PDO;
use Symphograph\Bicycle\Api\CurlAPI;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\CurlToken;
use Symphograph\Bicycle\Token\SessionToken;
use Symphograph\Bicycle\Token\Token;
use Symphograph\Bicycle\Errors\AuthErr;
use Throwable;

class Session extends SessionDTO
{
    use ModelTrait;
    use ModelCookieTrait;
    public array $powers = [];

    public static function create(int $accountId): self
    {
        $agent = Agent::getSelf();

        try {
            $Session = new self();
            $Session->marker = self::createMarker();
            $Session->accountId = $accountId;
            $Session->client = Client::getName();
            $Session->firstIp = $_SERVER['REMOTE_ADDR'];
            $Session->lastIp = $_SERVER['REMOTE_ADDR'];
            $Session->createdAt = date('Y-m-d H:i:s');
            $Session->visitedAt = $Session->createdAt;
            $Session->platform = $agent->platform;
            $Session->browser = $agent->browser;
            $Session->device_type = $agent->device_type;
            $Session->ismobiledevice = $agent->ismobiledevice;
            $Session->putToDB();
            $Session->id = DB::lastId();
            return self::byId($Session->id);
        } catch (Throwable $e) {
            throw new AuthErr($e->getMessage(), 'Не удалось создать сессию');
        }
    }

    public static function byJWT(): self
    {
        if (empty($_POST['SessionToken']) || empty($_POST['AccessToken'])) {
            throw new AuthErr('tokens is empty', httpStatus: 400);
        }
        SessionToken::validation(jwt: $_POST['SessionToken']);
        AccessToken::validation(jwt: $_POST['AccessToken'], ignoreExpire: true);

        $sessionToken = Token::toArray($_POST['SessionToken']);
        $accessToken = Token::toArray($_POST['AccessToken']);

        $Session = self::byMarker($sessionToken['jti']) or
        throw new AuthErr('Session does not exist', 'Session does not exist');

        if ($accessToken['iat']->getTimestamp() !== strtotime($Session->visitedAt)) {
            throw new AuthErr('Invalid tokenTime', 'Invalid tokenTime');
        }

        return $Session;
    }

    public function getPowers(): array
    {
        $clientGroup = Client::getGroupName($this->client);
        $User = User::bySess($this->marker);
        $qwe = qwe("
            select powerId 
            from nn_powers 
            where userId = :userId
            and clientGroup = :clientGroup",
            ['userId' => $User->id, 'clientGroup' => $clientGroup]
        );
        return $qwe->fetchAll(PDO::FETCH_COLUMN) ?? [];
    }

    public function curlPowers(): void
    {
        //TODO сделать переменную клиента
        $jwt = CurlToken::create([1]);
        $TeleUser = $this->getTelegramUser();
        if (!$TeleUser) {
            return;
        }
        $curl = new CurlAPI(
            'ussoStaff',
            '/api/powers.php',
            ['method' => 'byTelegram', 'telegramId' => $TeleUser->id],
            $jwt
        );
        $response = $curl->post() or throw new AuthErr();
        $this->powers = $response->data ?? [];
        $User = User::byAccount($TeleUser->accountId);
        self::savePowers($User->id, 'usso');
    }

    private function savePowers(int $userId, string $clientGroup): void
    {
        qwe("
            delete from nn_powers 
            where userId = :userId 
            and clientGroup = :clientGroup",
            ['userId' => $userId, 'clientGroup' => $clientGroup]
        );
        foreach ($this->powers as $powerId) {
            qwe("
                replace into nn_powers 
                    (userId, powerId, clientGroup) 
                    VALUES 
                    (:userId, :powerId, :clientGroup)",
                ['userId' => $userId, 'powerId' => $powerId, 'clientGroup' => $clientGroup]
            );
        }
    }

    public function getTelegramUser(): TeleUser|false
    {
        $User = User::bySess($this->marker);
        $accounts = Account::getListByUser($User->id);
        foreach ($accounts as $account) {
            if ($account->authType === 'telegram') {
                return TeleUser::byAccountId($account->id);
            }
        }
        return false;
    }

}