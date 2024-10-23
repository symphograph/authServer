<?php

use App\Models\Account;
use App\Models\Device;
use App\Models\Session;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\SessionToken;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Token\Token;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

qwe("START TRANSACTION");

$Device = Device::createOrUpdate();
$Account = Account::create('default');
$Session = Session::create($Account->id);

$Device->linkToSess($Session->id);
$Device->linkToAccount($Account->id);

$SessionToken = SessionToken::create($Session->marker, $Session->visitedAt);
$AccessToken = AccessToken::create(
    uid: 0,
    accountId: $Account->id,
    powers: [],
    createdAt: $Session->visitedAt
);
qwe("COMMIT");

$data = [
    'SessionToken' => $SessionToken,
    'AccessToken'  => $AccessToken
];
if(Env::isDebugMode()){
    $data['Account'] = $Account;
    $data['Session'] = $Session;
    $data['Device'] = $Device;
    $data['tokenData'] = Token::toArray($AccessToken);
}

Response::data($data);