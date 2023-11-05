<?php

namespace App;

use Symphograph\Bicycle\Env\Server\ServerEnv;
use Symphograph\Bicycle\HTTP\Cookie;
use App\DTO\{UserDTO, SessionDTO};
use App\Models\{Account, Device, Session, User};
use Symphograph\Bicycle\PDO\DB;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\ITF\SocialAccountITF;
use Symphograph\Bicycle\Token\{AccessToken, SessionToken, SessionTokenData};

class AuthCallBack
{
    public static function checkReferer(string $path): void
    {
        (ServerEnv::HTTP_REFERER() ?? '')
        ===
        "https://" . ServerEnv::SERVER_NAME() . "$path"
        or throw new AuthErr('unknown referer');
    }

    public static function setCookies(): void
    {
        $opts = Cookie::opts(600, '/auth/', 'None');
        $sessTokenData = new SessionTokenData($_POST['SessionToken']);
        Cookie::set('origin', ServerEnv::HTTP_ORIGIN(), $opts);
        Cookie::set(SessionDTO::cookieName, $sessTokenData->marker, $opts);

    }

    public static function loginChecks(): void
    {
        Config::isClientOrigin() or
        throw new AuthErr('invalid origin', 'Источник не определён');

        AccessToken::validation($_POST['AccessToken'] ?? '');
        SessionToken::validation($_POST['SessionToken'] ?? '');

        $sessTokenData = new SessionTokenData($_POST['SessionToken']);
        $Sess = Session::byMarker($sessTokenData->marker) or
        throw new AuthErr('Session does not exist', 'Попробуйте еще раз');
        self::setCookies();
    }

    /**
     * @param SocialAccountITF|bool $existingUser
     * @param string $authType
     * @param SocialAccountITF $responseUser
     * @return void
     */
    public static function accountTransaction(
        SocialAccountITF|bool $existingAccount,
        string $authType,
        SocialAccountITF $responseUser
    ): void
    {
        DB::pdo()->beginTransaction();
            $Sess = Session::byMarker($_COOKIE[SessionDTO::cookieName] ?? '') or
            throw new AuthErr('session does not exist');

            if (!$existingAccount) {
                $Account = Account::create($authType);
            } else {
                $Account = Account::byId($existingAccount->accountId);
            }

            $Sess->accountId = $Account->id;
            $Sess->putToDB();

            $responseUser->accountId = $Account->id;
            $responseUser->putToDB();

            $url = $_COOKIE['origin']
                ?? throw new AuthErr('origin is missed', 'Не найден адрес перенаправления');

            $Device = Device::bySessId($Sess->id) or throw new AuthErr('unknown device');
            $Device->linkToAccount($Account->id);
        DB::pdo()->commit();
        header("Location: $url/auth/callback");
    }
}