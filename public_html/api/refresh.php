<?php

use App\Models\Account;
use App\Models\Device;
use App\Models\Session;
use App\Models\User;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\SessionToken;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Token\Token;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

$Session = Session::byJWT();
$Session->lastIp = $_SERVER['REMOTE_ADDR'];
$Session->visitedAt = date('Y-m-d H:i:s');

$Account = Account::byId($Session->accountId);
$Account->initData();


$Device = Device::byCookie();
Device::isLinked($Device->id, $Account->id)
or throw new AuthErr('Unknown device', 'Unknown device');

$User = User::byAccount($Session->accountId);
$Session->curlPowers();
$User->setCookMarker();

$SessionToken = SessionToken::create($Session->marker, $Session->visitedAt);
$AccessToken = AccessToken::create(
    $User->id, $Account->id,
    $Session->powers,
    $Session->visitedAt,
    $Account->authType,
    $Account->Avatar->fileName ?? 'init_ava.png'
);

$data = [
    'SessionToken' => $SessionToken,
    'AccessToken'  => $AccessToken,
];
if (Env::isDebugMode()) {
    $data['Session'] = $Session;
    $data['tokenData'] = Token::toArray($AccessToken);
}

$Session->putToDB();
Response::data($data);