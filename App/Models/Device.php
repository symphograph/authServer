<?php

namespace App\Models;

use App\DTO\DeviceDTO;
use PDO;
use Symphograph\Bicycle\Helpers\DateTimeHelper;
use Symphograph\Bicycle\PDO\DB;
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
        return self::byBind($parent);
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
        $Device->fingerPrint = self::createFingerPrint();
        $Device->platform = Agent::getSelf()->platform;
        $Device->ismobiledevice = Agent::getSelf()->ismobiledevice;
        $Device->browser = Agent::getSelf()->browser;
        $Device->putToDB();
        $Device->id = DB::lastId();
        $Device->setCookie(self::cookDuration);
        return $Device;
    }

    public function update(): void
    {
        $this->visitedAt = date('Y-m-d H:i:s');
        $this->fingerPrint = self::createFingerPrint();
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

    public function unlinkAccount(int $accountId): void
    {
        DB::qwe("
            delete from deviceAccount 
            where accountId = :accountId
            and deviceId = :deviceId",
            ['accountId'=>$accountId, 'deviceId'=>$this->id]
        );
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

    /**
     * @param int $accountId
     * @return self[]
     */
    public function listOther(int $accountId): array
    {
        $qwe = qwe("
            select devices.* from devices 
            inner join deviceAccount 
                on devices.id = deviceAccount.deviceId
                and deviceAccount.accountId = :accountId
                and devices.id != :curDeviceId",
            ['accountId'=>$accountId, 'curDeviceId'=> $this->id]
        );
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class) ?? [];
    }

    protected function datesToISO_8601(): void
    {
        $this->createdAt = DateTimeHelper::dateFormatFeel($this->createdAt, 'c');
        $this->visitedAt = DateTimeHelper::dateFormatFeel($this->visitedAt, 'c');
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

    public static function createFingerPrint(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        // Дополнительная информация, которую вы можете использовать, если это необходимо
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $encoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';

        // Объединение всех параметров для создания уникальной строки
        $fingerprintData = $userAgent . $ipAddress . $acceptLanguage . $encoding;

        // Примените хэш-функцию (например, SHA-256) для создания уникального значения
        return hash('sha256', $fingerprintData);
    }

}