<?php

use JetBrains\PhpStorm\Language;
use Symphograph\Bicycle\PDO\DB;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Env\Server\ServerEnvCli;
use Symphograph\Bicycle\Env\Server\ServerEnvHttp;
use Symphograph\Bicycle\Env\Server\ServerEnvITF;

function printr($var): void
{
    if(!Env::isDebugMode())
        return;
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

function qwe(#[Language("SQL")] string $sql, array $args = [], string $connectName = 'default'): false|PDOStatement
{
    return DB::qwe($sql, $args, $connectName);
}

function getRoot(): string
{
    return dirname(__DIR__);
}

function getServerEnvClass(): ServerEnvITF
{
    global $ServerEnv;
    if(isset($ServerEnv)) {
        return $ServerEnv;
    }
    if (PHP_SAPI === 'cli') {
        $ServerEnv = new ServerEnvCli();
    } else {
        $ServerEnv = new ServerEnvHttp();
    }
    return $ServerEnv;
}