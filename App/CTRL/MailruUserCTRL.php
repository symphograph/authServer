<?php

namespace App\CTRL;

use App\Models\Account;

use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Auth\Mailru\MailruUser;
use Symphograph\Bicycle\Env\Services\Client;
use Symphograph\Bicycle\Errors\AccountErr;
use Symphograph\Bicycle\Errors\NoContentErr;
use Symphograph\Bicycle\Errors\ValidationErr;
use Symphograph\Bicycle\Helpers;
use Symphograph\Bicycle\HTTP\Request;
use Symphograph\Bicycle\PDO\DB;

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
        Request::checkEmpty(['MailruUser', 'createdAt', 'visitedAt']);

        Helpers::isDate($_POST['createdAt'], 'Y-m-d H:i:s') or throw new ValidationErr();

        Helpers::isDate($_POST['visitedAt'], 'Y-m-d H:i:s') or throw new ValidationErr();

        $data = json_decode($_POST['MailruUser']);

        DB::pdo()->beginTransaction();
            $newMailruUser = MailruUser::byBind($data);
            $existsMailruUser = MailruUser::byEmail($newMailruUser->email);
            if ($existsMailruUser) {
                $Account = Account::byId($existsMailruUser->accountId)->initData();
                Response::data([
                    'newMailruUser' => $existsMailruUser,
                    'avaFilename' => $Account->avaFileName
                ]);
            }


            $Account = Account::create('mailru');
            $Account->createdAt = $_POST['createdAt'];
            $Account->visitedAt = $_POST['visitedAt'];
            $Account->putToDB();
            $newMailruUser->accountId = $Account->id;
            $newMailruUser->putToDB();
            $Account->initData();
        DB::pdo()->commit();
        Response::data(['newMailruUser' => $newMailruUser, 'avaFilename' => $Account->avaFileName]);
    }
}