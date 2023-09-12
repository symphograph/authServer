<?php

use JetBrains\PhpStorm\Language;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Env\Env;

function printr($var): void
{
    if(!Env::isDebugMode())
        return;
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

function qwe(#[Language("SQL")] string $sql, array $args = null): bool|PDOStatement
{
    global $DB;
    if(!isset($DB)){
        $DB = new DB();
    }
    return $DB->qwe($sql,$args);
}