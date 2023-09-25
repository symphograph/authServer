<?php

use App\AuthCallBack;
use App\Models\Device;
use Symphograph\Bicycle\Auth\Telegram\Telegram;
use Symphograph\Bicycle\Env\Env;


require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

if(isset($_GET['logout'])) {
    setcookie('tg_user', '');
    $url = $_COOKIE['origin'] ?? false;
    header("Location: $url/auth/callback");
}

AuthCallBack::loginChecks();

echo Telegram::widgetPage(Env::getTelegramSecrets()->loginPageTitle, 'auth/telegram/callback.php');