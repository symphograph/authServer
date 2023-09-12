<?php

use App\Models\Account;
use App\Models\Device;
use App\Models\Session;
use App\Models\User;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\SessionToken;
use Symphograph\Bicycle\Api\Response;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

qwe("START TRANSACTION");

$Device = Device::createOrUpdate();

$User = User::create();
$Account = Account::create($User->id, 'default');
$Device->linkToAccount($Account->id);
$Session = Session::create($Account->id);

$SessionToken = SessionToken::create($Session->marker, $Session->visitedAt);
$AccessToken = AccessToken::create(
    uid: $User->id,
    accountId: $Account->id,
    powers: $Session->powers,
    createdAt: $Session->visitedAt
);
qwe("COMMIT");

$data = [
    'SessionToken' => $SessionToken,
    'AccessToken'  => $AccessToken
];
if(Env::isDebugMode()){
    $data['User'] = $User;
    $data['Account'] = $Account;
    $data['Session'] = $Session;
}

Response::data($data);