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

function qwe(#[Language("SQL")] string $sql, array $args = []): false|PDOStatement
{
    return DB::qwe($sql, $args);
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
        return new ServerEnvCli();
    }
    return new ServerEnvHttp();
}