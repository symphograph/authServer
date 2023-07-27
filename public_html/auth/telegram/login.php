<?php

use App\Models\Session;
use Symphograph\Bicycle\Auth\Telegram\Telegram;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Token\{AccessToken, SessionToken, Token};

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

if(isset($_GET['logout'])) {
    setcookie('tg_user', '');
    $url = $_COOKIE['origin'] ?? false;
    header("Location: $url/auth/callback");
}

Config::isClientOrigin() or
throw new AuthErr('invalid origin', 'Источник не определён');

$TeleUser = new TeleUser();
$Telegram = new Telegram();

AccessToken::validation($_POST['AccessToken'] ?? '');
SessionToken::validation($_POST['SessionToken'] ?? '');


$jwtArray = Token::toArray($_POST['SessionToken']);

$Sess = Session::byId($jwtArray['jti']) or
throw new AuthErr('Session does not exist', 'Попробуйте еще раз');

setcookie('origin', $_SERVER['HTTP_ORIGIN'], Config::cookOpts(expires: time() + 600, path: '/auth/telegram'));
setcookie('sessionId', $jwtArray['jti'], Config::cookOpts(expires: time() + 600, path: '/auth/telegram'));

echo Telegram::widgetPage(Env::getTelegramSecrets()->loginPageTitle, 'auth/telegram/callback.php');