<?php

namespace App\Models;

use App\DTO\DeviceDTO;
use App\Env\Config;
use Symphograph\Bicycle\DB;
use App\Models\ModelCookieTrait;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Errors\AuthErr;

class Device extends DeviceDTO
{
    use ModelTrait;
    use ModelCookieTrait;

    public static function byCookie(): self|false
    {
        if(empty($_COOKIE[self::cookieName])){
            throw new AuthErr('cook is empty');
        }
        $Device = self::byMarker($_COOKIE[self::cookieName]);
        if(!$Device){
            self::unsetCookie();
            throw new AuthErr('Unknown device', 'Unknown device');
        }
        return $Device;
    }

    public static function createOrUpdate(): self
    {
        if (empty($_COOKIE[self::cookieName])) {
            return self::create();
        }
        $Device = Device::byMarker($_COOKIE[self::cookieName]);
        if (!$Device) {
            self::unsetCookie();
            throw new AuthErr('invalid device', 'Что-то не получилось. Попробуйте ещё раз');
        }
        $Device->visitedAt = date('Y-m-d H:i:s');
        $Device->putToDB();
        return $Device;
    }

    public static function create(): self
    {
        $Device = new self();
        $Device->marker = self::createMarker();
        $time = date('Y-m-d H:i:s');
        $Device->createdAt = $time;
        $Device->visitedAt = $time;
        $Device->putToDB();
        $Device->id = DB::lastId();
        $Device->setCookie(3600 * 24 * 365);
        return $Device;
    }

    public function linkToAccount(int $accountId): void
    {
        $params = [
            'deviceId'  => $this->id,
            'accountId' => $accountId,
            'linkedAt'  => date('Y-m-d H:i:s')
        ];
        DB::replace('deviceAccount', $params);
    }

    public static function isLinked(string $deviceId, int $accountId): bool
    {
        $qwe = qwe("
            select * from deviceAccount 
            where deviceId = :deviceId 
            and accountId = :accountId",
            ['deviceId' => $deviceId, 'accountId' => $accountId]
        );
        return $qwe && $qwe->rowCount();
    }
}