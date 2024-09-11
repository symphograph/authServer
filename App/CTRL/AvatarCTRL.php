<?php

namespace App\CTRL;



use App\Models\User;
use JetBrains\PhpStorm\NoReturn;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Files\FileImgCTRL;
use Symphograph\Bicycle\Files\UploadedImg;
use Symphograph\Bicycle\HTTP\Request;
use Symphograph\Bicycle\Token\AccessTokenData;

class AvatarCTRL extends FileImgCTRL
{
    #[NoReturn] public static function add(): void
    {
        User::auth([11]);
        Request::checkEmpty(['persId']);
        $persId = AccessTokenData::persId();
        if($persId !== $_POST['persId']){
            User::auth([3]);
        }
        $FileIMG = parent::addIMG(UploadedImg::getFile());
        $FileIMG->makeSizes([50, 100]);
        Response::data($FileIMG);
    }
}