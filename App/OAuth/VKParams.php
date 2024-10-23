<?php

namespace App\OAuth;

use App\Env\Env;

class VKParams extends Params
{
    const string type         = 'vkontakte';
    const string pageTitle    = 'Вход через VKонтакте';

    public function __construct()
    {
        $secrets = Env::getVKSecrets();
        parent::__construct($secrets);
    }
}