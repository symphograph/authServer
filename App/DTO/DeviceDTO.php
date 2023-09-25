<?php

namespace App\DTO;

use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\DTO\DTOTrait;

class DeviceDTO extends DTO
{
    use DTOTrait;
    use DTOCookieTrait;
    const tableName = 'devices';
    const cookieName = 'Mozart';
    public int    $id;
    public string $marker;
    public string $createdAt;
    public string $visitedAt;

    public static function bySessId(int $sessId): self|false
    {
        $qwe = qwe("
            select devices.* from devices 
            inner join autht.deviceSess dS 
            on devices.id = dS.deviceId
            and dS.sessId = :sessId",
            ['sessId' => $sessId]
        );
        return $qwe->fetchObject(self::class);
    }

}