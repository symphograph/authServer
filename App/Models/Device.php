<?php

namespace App\Models;

use App\DTO\DeviceDTO;
use App\Env\Config;
use Symphograph\Bicycle\DB;
use App\Models\ModelCookieTrait;
use Symphograph\Bicycle\DTO\ModelTrait;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Logs\ErrorLog;

class Device extends DeviceDTO
{
    use ModelTrait;
    use ModelCookieTrait;
    const cookDuration = 31536000;

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

    public static function bySessId(int $sessId): self|false
    {
        $parent = parent::bySessId($sessId);
        $self = new self();
        $self->bindSelf($parent);
        return $self;
    }

    public static function createOrUpdate(): self
    {
        if (empty($_COOKIE[self::cookieName])) {
            return self::create();
        }
        $Device = Device::byMarker($_COOKIE[self::cookieName]);
        if (!$Device) {
            ErrorLog::writeMsg('device does not exist');
            return self::create();
        }
        $Device->update();
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
        $Device->setCookie(self::cookDuration);
        return $Device;
    }

    public function update(): void
    {
        $this->visitedAt = date('Y-m-d H:i:s');
        $this->putToDB();
        $this->setCookie(self::cookDuration);
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

    public function linkToSess(int $sessId): void
    {
        $params = [
            'deviceId'  => $this->id,
            'sessId' => $sessId,
            'linkedAt'  => date('Y-m-d H:i:s')
        ];
        DB::replace('deviceSess', $params);
    }

    public static function listByAccount(int $accountId): array
    {
        $qwe = qwe("
            select devices.* from devices 
            inner join deviceAccount 
                on devices.id = deviceAccount.deviceId
                and deviceAccount.accountId = :accountId",
            ['accountId'=>$accountId]
        );
        return $qwe->fetchAll(\PDO::FETCH_CLASS, self::class) ?? [];
    }

    public static function isLinkedToSess(string $deviceId, int $sessId): bool
    {
        $qwe = qwe("
            select * from deviceSess
            where deviceId = :deviceId 
            and sessId = :sessId",
            ['deviceId' => $deviceId, 'sessId' => $sessId]
        );
        return $qwe && $qwe->rowCount();
    }

    public static function isLinkedToAccount(string $deviceId, int $accountId): bool
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