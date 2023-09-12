<?php

use App\AuthCallBack;
use App\Models\Account;
use App\Models\Client;
use App\Models\Device;
use App\Models\Session;
use App\Models\User;
use Symphograph\Bicycle\Auth\Discord\DiscordApi;
use Symphograph\Bicycle\Auth\Discord\DiscordUser;
use Symphograph\Bicycle\Errors\AuthErr;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
$authType = 'discord';
$code = $_GET['code'] ?? throw new AuthErr('invalid code');

if(empty($_COOKIE['discordState']) || empty($_GET['state']) || ($_COOKIE['discordState'] !== $_GET['state'])){
    throw new AuthErr('invalid state');
}

$responseUser = DiscordApi::getUser()
or throw new AuthErr('DiscordUser is error');

$existingUser = DiscordUser::byId($responseUser->id);

AuthCallBack::accountTransaction($existingUser, $authType, $responseUser);