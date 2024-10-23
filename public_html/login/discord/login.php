<?php

use App\AuthCallBack;
use Symphograph\Bicycle\Auth\Discord\DiscordApi;


require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

AuthCallBack::loginChecks();
DiscordApi::login();