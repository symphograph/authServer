<?php


use App\CTRL\UserCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
if (empty($_POST['method'])) {
    throw new ValidationErr();
}

match ($_POST['method']) {
    'getForceByContacts' => UserCTRL::getForceByContacts(),
    default => throw new ApiErr()
};