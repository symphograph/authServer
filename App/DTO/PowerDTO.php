<?php

namespace App\DTO;

use Symphograph\Bicycle\DTO\BindTrait;

class PowerDTO
{
    use BindTrait;

    public ?int    $id;
    public ?string $name;
    public ?string $sname;
    public ?int    $lvl;
    public ?int    $siteVisible;
    public ?string $parent;
}