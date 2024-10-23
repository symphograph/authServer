<?php

namespace App\OAuth;


use App\Env\Env;

class MailRuParams extends Params
{
    const string type         = 'mailru';
    const string pageTitle    = 'Вход через MailRu';

    public readonly string $botName;


    public function __construct()
    {
        $secrets = Env::getMailruSecrets();
        $this->botName = $secrets->bot_name;
        parent::__construct();
    }
}