<?php

namespace App\DTO;

use App\ITF\UserITF;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\DTO\DTOTrait;

class UserDTO extends DTO implements UserITF
{
    use DTOTrait;
    use DTOCookieTrait;
    const tableName = 'users';
    const cookieName = 'Haydn';

    public int     $id;
    public string  $marker;
    public ?int    $parentId;
    public string  $createdAt;
    public string  $visitedAt;
    public ?string $email;
    public ?string $emailConfirmedAt;

    protected static function byChild(UserITF $childObject): self
    {
        $objectDTO = new self();
        $objectDTO->bindSelf($childObject);
        return $objectDTO;
    }

    public function putToDB(): void
    {
        $params = DB::initParams($this);
        DB::replace(self::tableName, $params);
    }
}