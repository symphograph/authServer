<?php

namespace App\DTO;


use App\Env\Config;
use Symphograph\Bicycle\HTTP\Cookie;

trait DTOCookieTrait
{
    public static function byMarker(string $marker): self|bool
    {
        $tableName = self::tableName;
        $qwe = qwe("select * from $tableName where marker = :marker", ['marker' => $marker]);
        return $qwe->fetchObject(self::class);
    }

    public function setCookie(int $duration = 0, $path = '/'): void
    {
        $opts = Cookie::opts($duration, $path, 'None');
        Cookie::set(self::cookieName, $this->marker, $opts);
    }

    public static function unsetCookie(): void
    {
        setcookie(self::cookieName, '', Config::cookOpts(expires: -3600 * 24 * 366, samesite: 'None'));
        unset($_COOKIE[self::cookieName]);
    }

    public static function createMarker(): string
    {
        return bin2hex(random_bytes(12));
    }
}
