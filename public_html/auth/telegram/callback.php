<?php

use App\Models\{Account, Client, Device, Session, User};
use App\AuthCallBack;
use Symphograph\Bicycle\Auth\Telegram\{Telegram, TeleUser};
use Symphograph\Bicycle\Errors\{AppErr, AuthErr};

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

$authType = 'telegram';

AuthCallBack::checkReferer('/auth/telegram/login.php');

$responseUser = Telegram::auth()
    or throw new AuthErr('telegram auth error');

$existingUser = TeleUser::byId($responseUser->id);

AuthCallBack::accountTransaction($existingUser, $authType, $responseUser);
