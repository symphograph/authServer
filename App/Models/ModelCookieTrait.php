<?php

namespace App\Models;

trait ModelCookieTrait
{
    public static function byMarker(string $id): self|bool
    {
        $parent = parent::byMarker($id);
        if(!$parent) return false;
        return self::byBind($parent);
    }

}