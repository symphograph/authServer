<?php

namespace App\Models;

use PDO;
use Symphograph\Bicycle\Api\CurlAPI;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Errors\MyErrors;
use Symphograph\Bicycle\Token\CurlToken;

class Account
{
    const authTypes = [
        'default',
        'telegram',
        'server',
        'vk',
        'discord',
        'email'
    ];
    public int    $id;
    public int    $userId;
    public string $authType;
    public string $created;
    public ?string $avaFileName;

    public static function create(User $User, string $authType): self
    {
        try {
            $Account = new self();
            $Account->id = DB::createNewID('accounts', 'id');
            $Account->userId = $User->id;
            $Account->authType = $authType;
            $Account->created = date('Y-m-d H:i:s');
            $Account->putToDB();
            $Account = self::byId($Account->id) or throw new AppErr('Account');
        } catch (MyErrors) {
            throw new AuthErr('Account was not created', 'Не удалось создать аккаунт');
        }
        return $Account;
    }

    public static function byId(int $id): self|bool
    {
        $qwe = qwe("select * from accounts where id = :id", ['id' => $id]);
        return $qwe->fetchObject(self::class);
    }

    public static function isStaff(int $teleId): bool
    {
        $curl = new CurlAPI(
            'ussoStaff',
            '/curl/get/pers.php',
            ['method' => 'byTeleId', 'teleId' => $teleId]
        );

        return boolval($curl->post()->result ?? false);
    }

    /**
     * @return self[]
     */
    public static function getListByUser(int $userId): array
    {
        $qwe = qwe("select * from accounts where userId = :userId", ['userId' => $userId]);
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class) ?? [];
    }



    public function putToDB(): void
    {
        $params = DB::initParams($this);
        DB::replace('accounts', $params);
    }
}