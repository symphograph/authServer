<?php

use App\Models\Account;
use App\Models\Client;
use App\Models\Device;
use App\Models\Session;
use App\Models\User;
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Env\Server\ServerEnv;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\SessionToken;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Token\Token;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$Session = Session::byJWT();
$Session->lastIp = ServerEnv::REMOTE_ADDR();
$Session->visitedAt = date('Y-m-d H:i:s');


$Device = Device::byCookie();
Device::isLinkedToSess($Device->getId(), $Session->id)
or throw new AuthErr('Unknown device', 'Unknown device');
$Device->update();


$Account = Account::byId($Session->accountId);
$Account->initData();


$SessionToken = SessionToken::create($Session->marker, $Session->visitedAt);
$AccessToken = AccessToken::create(
    $Account->userId ?? 0,
    $Account->id,
    $Account->getPowers($Session->client),
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
$Account->visitedAt = $Session->visitedAt;
$Account->putToDB();
$Session->putToDB();
Response::data($data);