<?php

use App\AuthCallBack;
use App\Models\Session;
use Symphograph\Bicycle\Auth\Vkontakte\Vkontakte;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Token\{AccessToken, SessionToken, SessionTokenData, Token};

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

AuthCallBack::loginChecks();

echo Vkontakte::widgetPage();