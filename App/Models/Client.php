<?php

namespace App\Models;

use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Errors\AccessErr;
use Symphograph\Bicycle\Errors\AppErr;

class Client
{
    public static function getName(): string
    {
        $client = array_search($_SERVER['HTTP_ORIGIN'] ?? [], Env::getClientDomains('https://'));
        if(empty($client)){
            throw new AppErr('client is empty', 'Клиент не найден');
        }
        return $client;
    }

    public static function getGroupName(string $clientName): string
    {
        $groupName = Env::getClientGroups()[$clientName] ?? false;
        if(empty($groupName)){
            throw new AppErr('clientGroup is empty', 'Клиент не найден');
        }
        return $groupName;
    }

    public static function authServer(): void
    {
        Env::isServerIp() or  throw new AccessErr();
        $Account = Account::byJwt($_SERVER['HTTP_ACCESSTOKEN']);
        if($Account->authType !== 'server'){
            throw new AccessErr();
        }
    }
}