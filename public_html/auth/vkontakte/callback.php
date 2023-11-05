<?php

use App\AuthCallBack;
use App\Profiles\VkUserApi;
use Symphograph\Bicycle\Auth\Vkontakte\VkUser;
use Symphograph\Bicycle\Errors\AppErr;


require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
$authType = 'vkontakte';

AuthCallBack::checkReferer('/auth/vkontakte/login.php');


$responseUser = VkUser::byGet();
$existingUser = VkUser::byId($responseUser->uid);

$vkUserData = VkUserApi::byVkApi($responseUser->uid) or throw new AppErr('Network Err', 'Ошибка сети');
$responseUser->domain = $vkUserData->domain;

AuthCallBack::accountTransaction($existingUser, $authType, $responseUser);