<?php

use App\CTRL\MailruUserCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
if (empty($_POST['method'])) {
    throw new ValidationErr();
}
match ($_POST['method']) {
    'getById' => MailruUserCTRL::getById(),
    'getByEmail' => MailruUserCTRL::getByEmail(),
    'create' => MailruUserCTRL::create(),
    default => throw new ApiErr()
};