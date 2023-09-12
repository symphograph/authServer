<?php

namespace App\Models;

trait ModelCookieTrait
{
    public static function byMarker(string $id): self|bool
    {
        $ObjectDTO = parent::byMarker($id);
        if(!$ObjectDTO) return false;
        $selfObject = new self();
        $selfObject->bindSelf($ObjectDTO);
        return $selfObject;
    }

}