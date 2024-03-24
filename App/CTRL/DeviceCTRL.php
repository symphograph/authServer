<?php

namespace App\CTRL;

use App\Models\Account;
use App\Models\Device;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\HTTP\Request;
use Symphograph\Bicycle\Token\AccessTokenData;

class DeviceCTRL extends Device
{
    #[NoReturn] public static function otherList(): void
    {
        if(empty($_COOKIE['Mozart'])){
            throw new AuthErr();
        }
        $curDevice = Device::byMarker($_COOKIE['Mozart']);
        $accountId = AccessTokenData::accountId();
        $list = $curDevice->listOther($accountId);
        $devices = array_map(function ($device) {
            $device->datesToISO_8601();
            return $device;
        }, $list);

        Response::data($devices);
    }

    public static function unlinkByMarker(): void
    {
        $marker = $_POST['targetDeviceMarker'] ?? throw new ValidationErr();
        $accountId = AccessTokenData::accountId();
        $device = Device::byMarker($marker);
        $device->unlinkAccount($accountId);
        Response::success();
    }

    public static function unlinkByAccount(): void
    {
        $curAccountId = AccessTokenData::accountId();
        Request::checkEmpty(['accountId']);

        $Account = Account::byId($_POST['accountId'])
            or throw new AppErr("Account {$_POST['accountId']} does not exist");

        $device = Device::byCookie();
        $device->unlinkAccount($Account->id);
        Response::success();
    }

}