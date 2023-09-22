<?php

namespace App\Models;

use App\DTO\AccountDTO;
use App\DTO\UserDTO;
use PDO;
use Symphograph\Bicycle\Api\CurlAPI;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\CurlToken;
use Symphograph\Bicycle\Token\Token;
use Symphograph\Bicycle\DB;
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
            $User->marker = self::createMarker();
            $User->putToDB();
            $User->id = DB::lastId();
            return self::byId($User->id);
        } catch (Throwable $e) {
            self::unsetCookie();
            throw new AuthErr($e->getMessage(), 'Ошибка создания пользователя', 401);
        }
    }

    public function curlUpdateId(): void
    {
        $jwt = CurlToken::create([1]);
        $curl = new CurlAPI(
            'ussoSite',
            '/api/tickets/ticket.php',
            ['method' => 'updateUserId', 'oldId' => $this->id, 'newId' => $this->parentId],
            $jwt
        );
        $response = $curl->post() or throw new AuthErr();
    }

    public static function bySess(string $SessMarker): self|bool
    {
        $Sess = Session::byMarker($SessMarker) or
        throw new AppErr('session does not exist');
        $Account = AccountDTO::byId($Sess->accountId);
        return self::byId($Account->userId);
    }

    public static function byAccessToken(): self|bool
    {
        //printr($_SERVER);
        if (empty($_SERVER['HTTP_ACCESSTOKEN'])) {
            throw new AuthErr('tokens is empty', httpStatus: 400);
        }
        AccessToken::validation(jwt: $_SERVER['HTTP_ACCESSTOKEN']);
        $accessToken = Token::toArray($_SERVER['HTTP_ACCESSTOKEN']);
        //printr($accessToken);
        return self::byId($accessToken['uid']);

    }

    public static function byAccount(int $accountId): self|bool
    {
        $Account = Account::byId($accountId);
        return self::byId($Account->userId);
    }

    public static function byTelegram(int $telegramId): self|false
    {
        $TeleUser = TeleUser::byId($telegramId);
        return self::byAccount($TeleUser->accountId);
    }

    public function setCookMarker(): void
    {
        self::setCookie();
    }

}