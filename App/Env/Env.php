<?php

namespace App\Env;

readonly class Env extends \Symphograph\Bicycle\Env\Env
{
    public static function getLocation(): string
    {
       return self::isTest() ? 'tauth' : 'auth';
    }
}