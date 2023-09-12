<?php

namespace App\CTRL;

use App\Models\Account;
use App\Models\Client;
use App\Models\User;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Auth\Mailru\MailruUser;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\NoContentErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\Helpers;
use Symphograph\Bicycle\Token\AccessTokenData;
use Symphograph\Bicycle\Token\Token;

class MailruUserCTRL extends MailruUser
{
    public static function getById(): void
    {
        Client::authServer();
        $accountId = $_POST['accountId']
            ?? throw new ValidationErr();
        $MailruUser = self::byAccountId($accountId)
            or throw new AccountErr('MailruUser does not exist');
        Response::data($MailruUser);
    }

    public static function getByEmail(): void
    {
        Client::authServer();
        $email = $_POST['email']
            ?? throw new ValidationErr();
        $MailruUser = self::byEmail($email)
            or throw new NoContentErr();
        Response::data($MailruUser);
    }

    public static function create(): void
    {
        Client::authServer();
        $data = $_POST['MailruUser'] ?? throw new ValidationErr();

        $createdAt = $_POST['createdAt'] ?? throw new ValidationErr();
        Helpers::isDate($createdAt, 'Y-m-d H:i:s') or throw new ValidationErr();

        $visitedAt = $_POST['visitedAt'] ?? throw new ValidationErr();
        Helpers::isDate($visitedAt, 'Y-m-d H:i:s') or throw new ValidationErr();

        qwe("START TRANSACTION");
        $newMailruUser = new MailruUser();
        $newMailruUser->bindSelf(json_decode($data));
        $User = User::create();
        $Account = Account::create($User->id, 'mailru');
        $Account->createdAt = $createdAt;
        $Account->visitedAt = $visitedAt;
        $Account->putToDB();
        $newMailruUser->accountId = $Account->id;
        $newMailruUser->putToDB();
        $Account->initData();
        qwe("COMMIT");
        Response::data(['newMailruUser' => $newMailruUser, 'avaFilename' => $Account->avaFileName]);
    }
}