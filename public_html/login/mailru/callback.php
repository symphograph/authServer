<?php

use App\AuthCallBack;
use Symphograph\Bicycle\Auth\Mailru\MailruUser;
use Symphograph\Bicycle\Auth\Mailru\OAuthMailRu;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Errors\Auth\AuthErr;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$authType = 'mailru';

$secret = Env::getMailruSecrets();

if (!empty($_GET['error'])) {
    throw new AuthErr($_GET['error']);
}

if (empty($_GET['code'])) {
    // Самый первый запрос
    OAuthMailRu::goToAuth($secret);
}

// Пришёл ответ без ошибок после запроса авторизации
if (!OAuthMailRu::getToken($_GET['code'], $secret)) {
    throw new AuthErr('Error - no token by code');
}


//-------------------------------------------------------------------------
/*
 * На данном этапе можно проверить зарегистрирован ли у вас MailRu-юзер с id = OAuthMailRu::$user_id
 * Если да, то можно просто авторизовать его и не запрашивать его данные.
 */

$responseUser = OAuthMailRu::getUser()
or throw new AuthErr('telegram auth error');

$existingUser = MailruUser::byId($responseUser->id);

AuthCallBack::accountTransaction($existingUser, $authType, $responseUser);