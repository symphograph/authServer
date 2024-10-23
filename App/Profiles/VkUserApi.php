<?php

namespace App\Profiles;

use Symphograph\Bicycle\Auth\Vkontakte\VkUser;
use Symphograph\Bicycle\Env\Env;
use VK\Client\VKApiClient;

class VkUserApi extends VkUser
{
    public static function byVkApi(int|string $contactValue): VkUser|false
    {
        $vk = new VKApiClient();
        $access_token = Env::getVKSecrets()->serviceKey;
        $response = $vk->users()->get($access_token, [
            'user_ids'  => [$contactValue],
            'fields'    => ['domain', 'photo_100', 'photo_rec', 'photo'],
        ]);
        if(empty($response[0])){
            return false;
        }

        $vkUser = VkUser::byBind($response[0]);

        $vkUser->uid = $response[0]['id'];
        $vkUser->domain = $response[0]['domain'];
        $vkUser->photo_rec = $response[0]['photo_100'];
        return $vkUser;
    }
}