<?php

namespace App\Env;

use Symphograph\Bicycle\Env\Env;

class Config extends \Symphograph\Bicycle\Env\Config
{

    public static function initEndPoints(): void
    {
        self::checkOrigin();
        self::initEndPoint('/epoint/', ['POST', 'OPTIONS']);

        self::initEndPoint(
            '/curl/',
            ['GET', 'POST', 'OPTIONS'],
            [
                'Accept'             => 'application/json',
                'HTTP_AUTHORIZATION' => Env::getApiKey()
            ]
        );
    }



}