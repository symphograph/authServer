<?php

use App\Models\Account;
use App\Models\Session;
use App\Models\User;
use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\SessionToken;
use Symphograph\Bicycle\Api\Response;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

qwe("START TRANSACTION");
$User = User::create();
$Account = Account::create($User, 'default');
$Session = Session::create($Account->id);

$SessionToken = SessionToken::create($Session->id, $Session->lastTime);
$AccessToken = AccessToken::create(uid: $User->id, powers: $User->powers, createdAt: $Session->lastTime);
qwe("COMMIT");

$data = [
    'SessionToken'=>$SessionToken,
    'AccessToken' => $AccessToken
];
if(Env::isDebugMode()){
    $data['User'] = $User;
    $data['Account'] = $Account;
    $data['Session'] = $Session;
}
Response::data($data);