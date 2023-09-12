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

}