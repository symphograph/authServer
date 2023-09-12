<?php

use App\AuthCallBack;
use App\Models\Session;
use Symphograph\Bicycle\Auth\Vkontakte\Vkontakte;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Token\{AccessToken, SessionToken, SessionTokenData, Token};

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

Config::isClientOrigin() or
throw new AuthErr('invalid origin', 'Источник не определён');

AccessToken::validation($_POST['AccessToken'] ?? '');
SessionToken::validation($_POST['SessionToken'] ?? '');

$sessTokenData = new SessionTokenData($_POST['SessionToken']);
$Sess = Session::byMarker($sessTokenData->marker) or
throw new AuthErr('Session does not exist', 'Попробуйте еще раз');

AuthCallBack::setCookies();

echo Vkontakte::widgetPage('ЮССО VK вход', '/auth/vkontakte/callback.php');