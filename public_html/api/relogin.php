<?php

use App\Models\Account;
use App\Models\Device;
use App\Models\Session;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\Token\AccessTokenData;
use Symphograph\Bicycle\Token\SessionTokenData;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

$SessionToken = ($_POST['SessionToken'] ?? false)
or new ValidationErr();

$accountId = AccessTokenData::accountId();
$curAccount = Account::byId($accountId)
or throw new AccountErr("curAccount $curAccount does not exist");

$toAccountId = intval($_POST['toAccountId'] ?? false)
or new ValidationErr();

$toAccount = Account::byId($toAccountId)
or throw new AccountErr("toAccount $toAccountId does not exist");

if ($curAccount->userId !== $toAccount->userId) {
    throw new AccountErr(
        "curAccount: $curAccount->userId != toAccount:  $toAccount->userId",
        'Произошла чудовищная ошибка'
    );
}


$SessionTokenData = new SessionTokenData($_POST['SessionToken']);
$Sess = Session::byMarker($SessionTokenData->marker);
$Device = Device::bySessId($Sess->id);
Device::isLinkedToAccount($Device->id, $toAccount->id)
    or throw new AuthErr('needLogin', 'needLogin', 401);
$Sess->accountId = $toAccount->id;
$Sess->putToDB();
Response::success();