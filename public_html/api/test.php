<?php

use App\Env\Config;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
setcookie('testCook', '123', Config::cookOpts(expires: time()+60,samesite: 'None'));
if(empty($_COOKIE['testCook'])){
    echo 'empty';
}else{
    printr($_COOKIE);
}
