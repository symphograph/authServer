<?php

namespace App\CTRL;

use App\Models\Account;
use App\Models\Client;
use App\Models\User;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Auth\Discord\DiscordUser;
use Symphograph\Bicycle\Auth\Mailru\MailruUser;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\Auth\Vkontakte\VkUser;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\NoContentErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\Helpers;

class PowersCTRL
{
    public static function set(): void
    {
        Client::authServer();

        $powers = $_POST['powers'] ?? throw new ValidationErr();
        $clientGroup = $_POST['clientGroup'];

        Response::success();
    }
}