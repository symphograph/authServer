<?php

namespace App\DTO;

use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\DTO\DTOTrait;

class SessionDTO extends DTO
{
    use DTOTrait;
    use DTOCookieTrait;
    const tableName = 'sessions';
    const cookieName = 'Beethoven';

    public int     $id;
    public string  $marker;
    public int     $accountId;
    public string  $client;
    public ?string $token;
    public ?string $firstIp;
    public ?string $lastIp;
    public ?string $createdAt;
    public ?string $visitedAt;
    public ?string $platform;
    public ?string $browser;
    public ?string $device_type;
    public bool    $ismobiledevice;

}