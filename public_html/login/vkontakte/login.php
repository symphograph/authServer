<?php

use App\AuthCallBack;
use App\OAuth\VKParams;
use Symphograph\Bicycle\Auth\Vkontakte\Vkontakte;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

AuthCallBack::loginChecks();

$params = new VKParams();

echo Vkontakte::widgetPage(
    VKParams::pageTitle,
    $params->secrets->getAppId(),
    $params->callbackUrl
);