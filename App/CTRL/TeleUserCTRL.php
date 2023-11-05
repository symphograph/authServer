<?php

namespace App\CTRL;

use App\Models\Client;
use App\Models\User;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\NoContentErr;
use Symphograph\Bicycle\Errors\ValidationErr;

class TeleUserCTRL extends TeleUser
{
    public static function getByUserName(): void
    {
        Client::authServer();
        $username = $_POST['username'] ?? throw new ValidationErr();
        $TeleUser = TeleUser::byUserName($username)
            or throw new NoContentErr("TeleUser $username does not exists");
        Response::data($TeleUser);
    }
}