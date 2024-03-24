<?php

namespace App\Models;



use Symphograph\Bicycle\Env\Server\ServerEnv;
use Symphograph\Bicycle\FileHelper;
use Symphograph\Bicycle\ImgHelper;


class Avatar
{
    public ?string $externalUrl;
    public ?string $src      = '/img/avatars/init_ava.png';
    public ?string $fileName = 'init_ava.png';
    private const avaFolder = '/img/avatars/';
    private const emptyAva  = '/img/avatars/init_ava.png';
    private const censored  = ['df303c56aac75aed75398543cba7da4b.jpg'];

    public static function byAvaFileName(string $avaFileName): self|false
    {
        $Avatar = new self();
        if (in_array($avaFileName, self::censored)) {
            return $Avatar;
        }
        $src = self::avaFolder . $avaFileName;
        $fullPath = FileHelper::fullPath($src, true);

        if (!file_exists($fullPath)) {
            return false;
        }
        $Avatar->src = $src;
        $Avatar->fileName = $avaFileName;
        return $Avatar;
    }

    public static function byExternalUrl(string $externalUrl): self|false
    {
        try {
            $fileData = file_get_contents($externalUrl);
        } catch (\Throwable) {
            return false;
        }

        $fileName = md5($fileData);
        $filePath = dirname(ServerEnv::DOCUMENT_ROOT()) . '/uploadtmp/' . $fileName;

        FileHelper::fileForceContents($filePath, $fileData);
        $ext = ImgHelper::getExtension($filePath);
        FileHelper::delete($filePath);
        if (!$ext) {
            return false;
        }
        $filePath = ServerEnv::DOCUMENT_ROOT() . self::avaFolder . $fileName . '.' . $ext;
        if (!FileHelper::fileForceContents($filePath, $fileData)) {
            return false;
        }
        return self::byAvaFileName($fileName . '.' . $ext);
    }

    public static function byId()
    {

    }
}