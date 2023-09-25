<?php

namespace App\CTRL;

use App\DTO\AccountDTO;
use App\Models\Account;
use App\Models\Client;
use App\Models\Device;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Token\AccessTokenData;

class AccountCTRL extends Account
{
    public static function get(): void
    {
        $accountId = AccessTokenData::accountId();
        $Account = parent::byIdAndInit($accountId)
            or throw new AccountErr('not exist', 'Аккаунт не найден');

        Response::data($Account);
    }

    #[NoReturn] public static function list(): void
    {
        $Device = Device::byCookie();
        $list = Account::getListByDevice($Device->id);
        $list = Account::initDataInList($list);
        Response::data($list);
    }

    #[NoReturn] public static function transfer(): void
    {
        Client::authServer();
        $AccountDTO = new AccountDTO();
        $AccountDTO->bindSelf($_POST['account']);
        Response::success();
    }
}