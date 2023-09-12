<?php

use App\Models\Session;
use Symphograph\Bicycle\Auth\Yandex\Yandex;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\SessionToken;
use Symphograph\Bicycle\Token\SessionTokenData;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

echo Yandex::widgetPage();