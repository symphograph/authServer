<?php

namespace App\OAuth;

use App\Env\Env;

class TGParams extends Params
{
    const string type         = 'telegram';
    const string pageTitle    = 'Вход через Telegram';

    public function __construct()
    {
        $secrets = Env::getTelegramSecrets();
        parent::__construct($secrets);
    }
}