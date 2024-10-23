<?php

namespace App\OAuth;

use App\Env\Env;
use Symphograph\Bicycle\Auth\OAuthSecrets;
use Symphograph\Bicycle\Env\Server\ServerEnv;

class Params
{
    public readonly string $refUrl;
    public readonly string $callbackUrl;
    public readonly OAuthSecrets $secrets;


    public function __construct(OAuthSecrets $secrets)
    {
        $url = $this->getSelfUrl();

        $this->callbackUrl = $url . $this->getCallbackPath();
        $this->refUrl = $url . $this->getRefPath();
        $this->secrets = $secrets;
    }

    protected function getSelfUrl(): string
    {
        $serverName = ServerEnv::SERVER_NAME();
        $fold = Env::getLocation();
        return "https://$serverName/$fold";
    }

    protected function getRefPath(): string
    {
        return '/login/' . static::type . '/login.php';
    }

    protected function getCallbackPath(): string
    {
        return '/login/' . static::type . '/callback.php';
    }
}