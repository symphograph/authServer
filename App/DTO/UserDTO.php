<?php

namespace App\DTO;

use App\ITF\UserITF;
use Symphograph\Bicycle\PDO\DB;
use Symphograph\Bicycle\DTO\DTOTrait;

class UserDTO
{
    use DTOTrait;
    use DTOCookieTrait;
    const string tableName  = 'users';
    const string cookieName = 'Haydn';

    public int     $id;
    public string  $createdAt;
    public string  $visitedAt;
    public ?string $email;
    public ?string $emailConfirmedAt;

}