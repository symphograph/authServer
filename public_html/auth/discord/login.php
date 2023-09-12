<?php

use App\AuthCallBack;
use Symphograph\Bicycle\Auth\Discord\DiscordApi;


require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

AuthCallBack::loginChecks();
DiscordApi::login();