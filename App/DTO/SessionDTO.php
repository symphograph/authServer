<?php

namespace App\DTO;

use Symphograph\Bicycle\DTO\DTOTrait;

class SessionDTO
{
    use DTOTrait;
    use DTOCookieTrait;
    const string tableName  = 'sessions';
    const string cookieName = 'Beethoven';

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