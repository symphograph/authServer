<?php

namespace App\Models;

use PDO;
use Symphograph\Bicycle\Api\CurlAPI;
use Symphograph\Bicycle\Auth\Telegram\TeleUser;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Token\AccessToken;
use Symphograph\Bicycle\Token\CurlToken;
use Symphograph\Bicycle\Token\Token;
use Symphograph\Bicycle\DB;
use Symphograph\Bicycle\Errors\AuthErr;
use Throwable;

class User
{
    public int      $id;
    public ?int      $parentId;
    public string   $created;
    public string   $lastTime;
    public ?string  $email;
    public ?string  $emailConfirmedAt;
    public ?array   $powers = [];
    private ?string $powersConcat;


    public static function create(): self
    {
        try {
            $User = new User();
            $User->id = DB::createNewID('users', 'id');
            $User->created = date('Y-m-d H:i:s');
            $User->lastTime = $User->created;
            $User->putToDB();
            return self::byId($User->id);
        } catch (Throwable $e) {
            throw new AuthErr($e->getMessage(), 'Ошибка создания пользователя', 401);
        }
    }

    public static function byId(int $id): self|bool
    {
        $qwe = qwe("
            select *, 
            (
                select group_concat(powerId separator ',')  
                from nn_powers 
                where userId = :userId 
            ) as powersConcat 
            from users where id = :id",
            ['userId' => $id, 'id' => $id]
        );
        if (!$qwe->rowCount()) {
            return false;
        }
        $User = $qwe->fetchObject(self::class);

        if (!empty($User->powersConcat)) {
            $User->powers = explode(',', $User->powersConcat);
        }
        $User->powers = self::getPowers($User->id);
        return $User;
    }

    public static function getPowers(int $userId): array
    {
        $qwe = qwe("select powerId from nn_powers where userId = :userId", ['userId' => $userId]);
        return $qwe->fetchAll(PDO::FETCH_COLUMN) ?? [];
    }

    public function curlPowers(): void
    {
        $jwt = CurlToken::create(2, [1]);
        $TeleUser = $this->getTelegramUser();
        if(!$TeleUser) {
            return;
        }
        $curl = new CurlAPI(
            'ussoStaff',
            '/api/powers.php',
            ['method' => 'byTelegram', 'telegramId' => $TeleUser->id],
            $jwt
        );
        $response = $curl->post() or throw new AuthErr();
        $this->powers = $response->data ?? [];
        self::savePowers();
    }

    public function curlUpdateId(): void
    {
        $jwt = CurlToken::create(2, [1]);
        $curl = new CurlAPI(
            'ussoSite',
            '/api/tickets/ticket.php',
            ['method' => 'updateUserId', 'oldId' => $this->id, 'newId' => $this->parentId],
            $jwt
        );
        $response = $curl->post() or throw new AuthErr();
    }

    private function getTelegramUser(): TeleUser|false
    {
        $accounts = Account::getListByUser($this->id);
        foreach ($accounts as $account){
            if($account->authType == 'telegram'){
                return TeleUser::byAccountId($account->id);
            }
        }
        return false;
    }

    public static function bySess(string $sessionId): self|bool
    {
        $Sess = Session::byId($sessionId) or
        throw new AppErr('session does not exist');
        $qwe = qwe("
            select * from users 
            where id = (
                select userId from accounts 
                where id = :accountId
            )",
            ['accountId' => $Sess->accountId]
        );
        $User = $qwe->fetchObject(self::class);
        $User->powers = self::getPowers($User->id);
        return $User;
    }

    public static function byAccessToken(): self|bool
    {
        //printr($_SERVER);
        if (empty($_SERVER['HTTP_ACCESSTOKEN'])) {
            throw new AuthErr('tokens is empty', httpStatus: 400);
        }
        AccessToken::validation(jwt: $_SERVER['HTTP_ACCESSTOKEN']);
        $accessToken = Token::toArray($_SERVER['HTTP_ACCESSTOKEN']);
        //printr($accessToken);
        return self::byId($accessToken['uid']);

    }

    public static function byAccount(int $accountId): self|bool
    {
        $Account = Account::byId($accountId);
        return self::byId($Account->userId);
    }

    public function putToDB(): void
    {
        //$params = DB::initParams($this);
        $params = [];
        unset($this->powersConcat);
        foreach ($this as $k => $v) {
            if ($v === null) continue;
            if (is_array($v) || is_object($v)) {
                continue;
            }
            $v = is_bool($this->$k) ? intval($v) : $v;

            $params[$k] = $v;
        }
        DB::replace('users', $params);
        self::savePowers();
    }

    private function savePowers(): void
    {
        qwe("delete from nn_powers where userId = :userId", ['userId' => $this->id]);
        foreach ($this->powers as $powerId) {
            qwe("
                replace into nn_powers 
                    (userId, powerId) 
                    VALUES 
                    (:userId, :powerId)",
                ['userId' => $this->id, 'powerId' => $powerId]
            );
        }
    }
}