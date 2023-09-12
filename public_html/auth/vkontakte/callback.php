<?php

use App\AuthCallBack;
use App\Models\Account;
use App\Models\Session;
use App\Models\User;
use Symphograph\Bicycle\Auth\Vkontakte\VkUser;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\AuthErr;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
$authType = 'vkontakte';

AuthCallBack::checkReferer('/auth/vkontakte/login.php');


$responseUser = VkUser::byGet();

$existingUser = VkUser::byId($responseUser->uid);

AuthCallBack::accountTransaction($existingUser, $authType, $responseUser);