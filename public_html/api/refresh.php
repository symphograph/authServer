<?php

use App\Models\Account;
use App\Models\AccountList;
use App\Models\Device;
use App\Models\Session;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Env\Server\ServerEnv;
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
$curlPowers = (object) $Account->getPowers($Session->client);
$powers = $curlPowers->powers ?? [];
$persId = $curlPowers->persId ?? null;
$AccessToken = AccessToken::create(
    uid: $Account->userId ?? 0,
    accountId: $Account->id,
    powers: $powers,
    createdAt: $Session->visitedAt,
    authType: $Account->authType,
    avaFileName: $Account->Avatar->fileName ?? 'init_ava.png',
    persId: $persId
);

$data = [
    'SessionToken' => $SessionToken,
    'AccessToken'  => $AccessToken,
];
if (Env::isDebugMode()) {
    $data['Session'] = $Session;
    $data['tokenData'] = Token::toArray($AccessToken);
    $data['accounts'] = AccountList::byDevice($Device->getId())
        ->excludeDefaults()
        ->initData()
        ->getList();
}
$Account->visitedAt = $Session->visitedAt;
$Account->putToDB();
$Session->putToDB();

Response::data($data);