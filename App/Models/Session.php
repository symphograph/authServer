<?php

namespace App\Models;

use App\DTO\SessionDTO;
use PDO;
use Symphograph\Bicycle\Api\CurlAPI;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\PDO\DB;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Env\Server\ServerEnv;
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

    public static function create(int $accountId): self
    {
        $agent = Agent::getSelf();

        try {
            $Session = new self();
            $Session->marker = self::createMarker();
            $Session->accountId = $accountId;
            $Session->client = Client::getNameByOrigin()
                or throw new AppErr('client is empty', 'Клиент не найден');;
            $Session->firstIp = ServerEnv::REMOTE_ADDR();
            $Session->lastIp = ServerEnv::REMOTE_ADDR();
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
}