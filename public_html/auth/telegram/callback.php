<?php

use App\Models\{Account, Client, Device, Session, User};
use App\AuthCallBack;
use Symphograph\Bicycle\Auth\Telegram\{Telegram, TeleUser};
use Symphograph\Bicycle\Errors\{AppErr, AuthErr};

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$authType = 'telegram';

AuthCallBack::checkReferer('/auth/telegram/login.php');

$responseUser = Telegram::auth()
    or throw new AuthErr('telegram auth error');

$existingUser = TeleUser::byId($responseUser->id);

AuthCallBack::accountTransaction($existingUser, $authType, $responseUser);
