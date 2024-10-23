<?php

use App\AuthCallBack;
use App\OAuth\TGParams;
use Symphograph\Bicycle\Auth\Telegram\{Telegram, TeleUser};
use Symphograph\Bicycle\Errors\Auth\AuthErr;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$authType = 'telegram';

$refUrl = (new TGParams())->refUrl;
AuthCallBack::checkReferer($refUrl);

$responseUser = Telegram::auth()
    or throw new AuthErr('telegram auth error');

$existingUser = TeleUser::byId($responseUser->id);

AuthCallBack::accountTransaction($existingUser, $authType, $responseUser);
