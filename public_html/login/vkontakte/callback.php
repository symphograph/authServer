<?php

use App\AuthCallBack;
use App\OAuth\VKParams;
use App\Profiles\VkUserApi;
use Symphograph\Bicycle\Auth\Vkontakte\VkUser;
use Symphograph\Bicycle\Errors\AppErr;


require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
$authType = 'vkontakte';
$vkParams = new VKParams();
AuthCallBack::checkReferer($vkParams->refUrl);


$responseUser = VkUser::byGet();
$existingUser = VkUser::byId($responseUser->uid);

$vkUserData = VkUserApi::byVkApi($responseUser->uid) or throw new AppErr('Network Err', 'Ошибка сети');

/*
printr($existingUser);
printr($responseUser);
printr($vkUserData);
die();
*/
$responseUser->domain = $vkUserData->domain;


AuthCallBack::accountTransaction($existingUser, $authType, $responseUser);