<?php

namespace App\Models;

use PDO;
use Symphograph\Bicycle\DTO\AbstractList;
use Symphograph\Bicycle\Helpers\Arr;

class AccountList extends AbstractList
{

    /**
     * @var Account[]
     */
    protected array $list = [];

    /**
     * @param Contact[] $contacts
     * @return self
     */
    public static function byContacts(array $contacts): self
    {
        $AccountList = new self();
        foreach ($contacts as $contact){
            $Account = Account::byContact($contact);
            if($Account) {
                $AccountList->list[] = $Account;
            }
        }
        return $AccountList;
    }

    /**
     * @param int $deviceId
     * @return self
     */
    public static function byDevice(int $deviceId): self
    {
        $sql = "
            select accounts.* from accounts 
            inner join deviceAccount dA 
                on accounts.id = dA.accountId
                and deviceId = :deviceId";

        $params = compact('deviceId');
        return self::bySql($sql, $params);
    }

    public function excludeDefaults(): static
    {
        $arr = [];
        foreach ($this->list as $account){
            if($account->authType === 'default'){
                continue;
            }
            $arr[] = $account;
        }
        $this->list = $arr;
        return $this;
    }

    /**
     * @param int $userId
     * @return self
     */
    public static function byUser(int $userId): self
    {
        $AccountList = new self();
        $qwe = qwe("
            select accounts.* 
            from accounts 
            where userId = :userId",
            ['userId' => $userId]
        );
        $AccountList->list = $qwe->fetchAll(PDO::FETCH_CLASS, Account::class) ?? [];
        return $AccountList;
    }

    public function sortByCreatedAt(bool $desc = false): void
    {
        $this->list = Arr::sortMultiArrayByProp($this->list, ['createdAt' => $desc ? 'desc' : 'asc']);
    }

    public function sortByVisitedAt(bool $desc = false): void
    {
        $this->list = Arr::sortMultiArrayByProp($this->list, ['visitedAt' => $desc ? 'desc' : 'asc']);
    }

    /**
     * @return Account[]
     */
    public function getList(): array
    {
        return $this->list;
    }

    public function setUserId(int $userId): void
    {
        foreach ($this->list as $account) {
            $account->userId = $userId;
            $account->putToDB();
        }
    }

    public function unsetUserId(): void
    {
        foreach ($this->list as $account) {
            unset($account->userId);
            $account->putToDB();
        }
    }

    public function getFirstUserId(): int|false
    {
        $this->sortByCreatedAt();
        foreach ($this->list as $account) {
            if(!empty($account->userId)) {
                return $account->userId;
            }
        }
        return false;
    }

    #[\Override] public static function getItemClass(): string
    {
        return Account::class;
    }
}