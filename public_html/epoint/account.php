<?php

use App\CTRL\AccountCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
if (empty($_POST['method'])) {
    throw new ValidationErr();
}

match ($_POST['method']) {
    'get' => AccountCTRL::get(),
    'byId' => AccountCTRL::byId(),
    'getByContact' => AccountCTRL::getByContact(),
    'listByContacts' => AccountCTRL::listByContacts(),
    'transfer' => AccountCTRL::transfer(),
    'list' => AccountCTRL::list(),
    default => throw new ApiErr()
};