<?php

namespace App\CTRL;

use App\Models\Account;
use App\Models\Client;
use App\Models\User;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\Helpers;

class PowersCTRL
{
    public static function usso(): void
    {
        Client::authServer();
        $telegramId = $_POST['telegramId']
            ?? throw new ValidationErr();
        Helpers::isArrayIntList($_POST['powers'] ?? false)
            or throw new ValidationErr();

        $User = User::byTelegram($telegramId);
        if(!$User){
            $User = User::create();
            $Account = Account::create($User->id, 'telegram');
            $TeleUser = new TeleUser();
            $TeleUser->id = $telegramId;
            $TeleUser->accountId = $Account->id;
            $TeleUser->putToDB();
        }

    }
}