<?php

namespace App\CTRL;

use App\Models\User;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\Errors\AccountErr;

class TeleUserCTRL extends TeleUser
{
    public static function get(): void
    {
        $User = User::byAccessToken();
        $TeleUser = $User->getTelegramUser()
            or throw new AccountErr('account does not exist');
        Response::data($TeleUser);
    }
}