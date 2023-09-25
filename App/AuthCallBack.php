<?php

namespace App;

use Symphograph\Bicycle\HTTP\Cookie;
use App\DTO\{UserDTO, SessionDTO};
use App\Models\{Account, Device, Session, User};
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\ITF\SocialAccountITF;
use Symphograph\Bicycle\Token\{AccessToken, SessionToken, SessionTokenData};

class AuthCallBack
{
    public static function checkReferer(string $path): void
    {
        ($_SERVER['HTTP_REFERER'] ?? '')
        ===
        "https://{$_SERVER['SERVER_NAME']}$path"
        or throw new AuthErr('unknown referer');
    }

    public static function getUser(): User|bool
    {
        if (!empty($_COOKIE[SessionDTO::cookieName])) {
            $User = User::bySess($_COOKIE[SessionDTO::cookieName] ?? '')
            or throw new AppErr('user does not exist');
            return $User;
        }

        $User = User::byMarker($_COOKIE[UserDTO::cookieName]  ?? '');
        if (!empty($User)) {
            return $User;
        }

        User::unsetCookie();
        $User = User::bySess($_COOKIE[SessionDTO::cookieName] ?? '')
        or throw new AppErr('user does not exist');
        return $User;
    }

    public static function setCookies(): void
    {
        $opts = Cookie::opts(600, '/auth/', 'None');
        $sessTokenData = new SessionTokenData($_POST['SessionToken']);
        Cookie::set('origin', $_SERVER['HTTP_ORIGIN'], $opts);
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
        SocialAccountITF|bool $existingUser,
        string $authType,
        SocialAccountITF $responseUser
    ): void
    {
        DB::pdo()->beginTransaction();
            $Sess = Session::byMarker($_COOKIE[SessionDTO::cookieName] ?? '') or
            throw new AuthErr('session does not exist');

            $User = AuthCallBack::getUser();
            if (!$existingUser) {
                $Account = Account::create($User->id, $authType);
            } else {
                $Account = Account::byId($existingUser->accountId);
            }

            $Sess->accountId = $Account->id;
            $Sess->putToDB();

            $responseUser->accountId = $Account->id;
            $responseUser->putToDB();

            $parentUser = User::byAccount($Account->id);
            $parentUser->setCookMarker();
            $User->parentId = $parentUser->id;
            $User->putToDB();

            $url = $_COOKIE['origin']
                ?? throw new AuthErr('origin is missed', 'Не найден адрес перенаправления');

            $Device = Device::bySessId($Sess->id) or throw new AuthErr('unknown device');
            $Device->linkToAccount($Account->id);
        DB::pdo()->commit();
        header("Location: $url/auth/callback");
    }
}