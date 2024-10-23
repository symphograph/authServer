<?php

use App\AuthCallBack;
use App\OAuth\TGParams;
use Symphograph\Bicycle\Auth\Telegram\Telegram;


require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

if(isset($_GET['logout'])) {
    setcookie('tg_user', '');
    $url = $_COOKIE['origin'] ?? false;
    header("Location: $url/auth/callback");
}

AuthCallBack::loginChecks();

$tgParams = new TGParams();

$pageTitle = TGParams::pageTitle;
$botName = $tgParams->secrets->getAppId();
$callbackUrl = $tgParams->callbackUrl;

echo Telegram::widgetPage($pageTitle, $botName, $callbackUrl);