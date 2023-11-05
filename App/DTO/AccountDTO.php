<?php

namespace App\DTO;


use Symphograph\Bicycle\DTO\DTOTrait;

class AccountDTO
{
    use DTOTrait;
    const tableName = 'accounts';

    public int     $id;
    public ?int     $userId;
    public string  $authType;
    public string  $createdAt;
    public string  $visitedAt;
    public ?string $avaFileName;

}