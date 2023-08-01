<?php

use App\Models\{Account, Session, User};
use Symphograph\Bicycle\Auth\Telegram\{Telegram, TeleUser};
use Symphograph\Bicycle\Errors\{AppErr, AuthErr};

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
($_SERVER['HTTP_REFERER'] ?? '')
    ===
    "https://{$_SERVER['SERVER_NAME']}/auth/telegram/login.php"
    or die();

$TeleUser = Telegram::auth() or throw new AuthErr('telegram auth error');

qwe("START TRANSACTION");
$Sess = Session::byId($_COOKIE['sessionId'] ?? '') or
throw new AuthErr('session does not exist');

$User = User::bySess($_COOKIE['sessionId'] ?? '') or
throw new AppErr('user does not exist');

if(!$savedTeleUser = TeleUser::byId($TeleUser->id)){
    $Account = Account::create($User, 'telegram');
}else{
    $Account = Account::byId($savedTeleUser->accountId);
}


$Sess->accountId = $Account->id;
$Sess->putToDB();
$TeleUser->accountId = $Account->id;
$TeleUser->putToDB();
$User->curlPowers();

$url = $_COOKIE['origin'] ?? false or throw new AuthErr('origin is missed', 'Не найден адрес перенаправления');
qwe("COMMIT");

//header("Location: $domain/symphoauth");
header("Location: $url/auth/callback");
