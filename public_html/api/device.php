<?php

use App\CTRL\AccountCTRL;
use App\CTRL\DeviceCTRL;
use Symphograph\Bicycle\Errors\ApiErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
if (empty($_POST['method'])) {
    throw new ValidationErr();
}

match ($_POST['method']) {
    'otherList' => DeviceCTRL::otherList(),
    'unlinkByMarker' => DeviceCTRL::unlinkByMarker(),
    'unlinkByAccount' => DeviceCTRL::unlinkByAccount(),
    default => throw new ApiErr()
};