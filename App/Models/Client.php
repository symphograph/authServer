<?php

namespace App\Models;

use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Env\Server\ServerEnv;
use Symphograph\Bicycle\Errors\AccessErr;

class Client extends \Symphograph\Bicycle\Api\Client
{
    public static function authServer(): void
    {
        Env::isServerIp() or  throw new AccessErr();
        $Account = Account::byJwt(ServerEnv::HTTP_ACCESSTOKEN());
        if($Account->authType !== 'server'){
            throw new AccessErr();
        }
    }

}