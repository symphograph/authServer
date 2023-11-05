<?php

namespace App\DTO;


use PDO;
use Symphograph\Bicycle\DTO\DTOTrait;

class DeviceDTO
{
    use DTOTrait;
    use DTOCookieTrait;

    const tableName  = 'devices';
    const cookieName = 'Mozart';
    public string    $marker;
    public string    $createdAt;
    public string    $visitedAt;
    public string    $platform;
    public bool      $ismobiledevice;
    public string    $browser;
    protected int    $id;
    protected string $fingerPrint;

    public static function bySessId(int $sessId): self|false
    {
        $qwe = qwe("
            select devices.* from devices 
            inner join deviceSess dS 
            on devices.id = dS.deviceId
            and dS.sessId = :sessId",
            ['sessId' => $sessId]
        );
        return $qwe->fetchObject(self::class);
    }

    /**
     * Выбирает устройства, имеющие переданный fingerPrint
     *
     * @param string $fingerPrint
     * @return self[]
     */
    public static function byFingerPrint(string $fingerPrint): array
    {
        $qwe = qwe("select * from devices where fingerPrint = :fingerPrint", ['fingerPrint' => $fingerPrint]);
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class) ?? [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

}