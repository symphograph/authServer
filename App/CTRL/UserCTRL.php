<?php

namespace App\CTRL;

use App\Models\AccountList;
use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\NoContentErr;
use Symphograph\Bicycle\Errors\ValidationErr;

class UserCTRL
{
    public static function getForceByContacts(): void
    {
        Client::authServer();

        $contactList = $_POST['contactList'] ?? throw new ValidationErr();
        if(!is_array($contactList)){
            throw new ValidationErr();
        }

        $contacts = Contact::listByBind($contactList);
        $AccountList = AccountList::byContacts($contacts);
        if(empty($AccountList->getList())) {
            throw new NoContentErr();
        }

        $userId = $AccountList->getFirstUserId();
        if(empty($userId)) {
            $User = User::create();
        } else {
            $User = User::byId($userId);
            $User::delById($userId);
            $User->putToDB();
        }

        $AccountList->setUserId($User->id);
        Response::data(['user' => $User]);
    }
}