<?php

namespace App\Models;

use Symphograph\Bicycle\Env\Env;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\SessionToken;
use Symphograph\Bicycle\Token\Token;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Errors\AuthErr;
use Throwable;

class Session
{
    public string  $id;
    public int     $accountId;
    public string  $client;
    public ?string $token;
    public ?string $firstIp;
    public ?string $lastIp;
    public ?string $firstTime;
    public ?string $lastTime;
    public ?string $platform;
    public ?string $browser;
    public ?string $device_type;
    public bool    $ismobiledevice;

    public static function create(int $accountId): self
    {
        $agent = Agent::getSelf();
        $client = array_search($_SERVER['HTTP_ORIGIN'], Env::getClientDomains('https://'));
        try {
            $Session = new self();
            $Session->id = bin2hex(random_bytes(12));
            $Session->accountId = $accountId;
            $Session->client = $client;
            $Session->firstIp = $_SERVER['REMOTE_ADDR'];
            $Session->lastIp = $_SERVER['REMOTE_ADDR'];
            $Session->firstTime = date('Y-m-d H:i:s');
            $Session->lastTime = $Session->firstTime;
            $Session->platform = $agent->platform;
            $Session->browser = $agent->browser;
            $Session->device_type = $agent->device_type;
            $Session->ismobiledevice = $agent->ismobiledevice;
            $Session->putToDB();
            return self::byId($Session->id);
        } catch (Throwable $e) {
            throw new AuthErr($e->getMessage(), 'Не удалось создать сессию');
        }
    }

    public function refresh()
    {

    }

    public static function byId(string $id): self|bool
    {
        $qwe = qwe("
            select * from sessions 
            where id = :id",
            ['id' => $id]
        );
        return $qwe->fetchObject(self::class);
    }

    public static function byJWT(): self
    {
        if(empty($_POST['SessionToken']) || empty($_POST['AccessToken'])){
            throw new AuthErr('tokens is empty', httpStatus: 400);
        }
        SessionToken::validation(jwt: $_POST['SessionToken']);
        AccessToken::validation(jwt: $_POST['AccessToken'], ignoreExpire: true);

        $sessionToken = Token::toArray($_POST['SessionToken']);
        $accessToken = Token::toArray($_POST['AccessToken']);

        $Session = self::byId($sessionToken['jti']) or
        throw new AuthErr('Session does not exist', 'Session does not exist');

        if($accessToken['iat']->getTimestamp() !== strtotime($Session->lastTime)){
            //var_dump($accessToken['iat']->getTimestamp());
            //var_dump(strtotime($Session->lastTime));
            throw new AuthErr('Invalid tokenTime', 'Invalid tokenTime');
        }

        return $Session;
    }



    public function putToDB(): void
    {
        $params = DB::initParams($this);
        DB::replace('sessions', $params);
    }

    public static function cookOpts(
        int         $expires = 0,
        string      $path = '/',
        string|null $domain = null,
        bool        $secure = true,
        bool        $httponly = true,
        string      $samesite = 'Strict', // None || Lax  || Strict
        bool        $debug = false
    ) : array
    {
        if(!$expires){
            $expires = 60*60*24*30;
        }
        $expires = time() + $expires;
        //$domain = $domain ?? $_SERVER['SERVER_NAME'];

        if($debug){
            return [
                'expires'  => $expires,
                'path'     => $path,
                'domain'   => null,
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'None'
            ];
        }
        return [
            'expires'  => $expires,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite // None || Lax  || Strict
        ];
    }
}