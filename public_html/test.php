<?php

use Symphograph\Bicycle\HTTP\Cookie;

require_once dirname(__DIR__) . '/vendor/autoload.php';
$opts = Cookie::opts(domain: '.dllib.ru');
Cookie::set('testAuth1', 123, $opts);
printr($_SERVER);