<?php

use App\Models\Account;
use App\Models\Session;
use App\Models\User;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\SessionToken;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Token\Token;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';


$Session = Session::byJWT();
$Session->lastIp = $_SERVER['REMOTE_ADDR'];
$Session->lastTime = date('Y-m-d H:i:s');

$Account = Account::byId($Session->accountId);
$User = User::byAccount($Session->accountId);
$User->curlPowers();

$SessionToken = SessionToken::create($Session->id, $Session->lastTime);
$AccessToken = AccessToken::create($User->id, $User->powers, $Session->lastTime, $Account->authType);

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