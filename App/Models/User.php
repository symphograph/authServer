<?php

namespace App\Models;

use App\DTO\AccountDTO;
use App\DTO\UserDTO;
use PDO;
use Symphograph\Bicycle\Api\CurlAPI;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Env\Server\ServerEnv;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\CurlToken;
use Symphograph\Bicycle\Token\Token;
use Symphograph\Bicycle\PDO\DB;
use Symphograph\Bicycle\Errors\AuthErr;
use Throwable;

class User extends UserDTO
{
    use ModelTrait;
    use ModelCookieTrait;

    public static function create(): self
    {
        try {
            $User = new User();
            $User->createdAt = date('Y-m-d H:i:s');
            $User->visitedAt = $User->createdAt;
            $User->putToDB();
            $User->id = DB::lastId();
            return self::byId($User->id);
        } catch (Throwable $e) {
            self::unsetCookie();
            throw new AuthErr($e->getMessage(), 'Ошибка создания пользователя', 401);
        }
    }

    public static function byAccount(int $accountId): self|false
    {
        $account = Account::byId($accountId);
        if(empty($account->userId)){
            return false;
        }
        return self::byId($account->userId);
    }

    public static function auth(array $allowedPowers = []): void
    {
        AccessToken::validation(ServerEnv::HTTP_ACCESSTOKEN(), $allowedPowers);
    }
}