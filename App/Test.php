<?php

namespace App;

use App\Models\User;

class Test
{


    public static function user(): void
    {
        $user = new User();
        $user->id = 1;
        $user->createdAt = date('Y-m-d h:i:s');
        $user->putToDB();

    }
}