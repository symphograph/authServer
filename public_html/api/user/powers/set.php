<?php

use App\Models\User;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AuthErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

$id = $_POST['id'] ?? false
or throw new ValidationErr();

is_array($_POST['powers'] ?? false)
or throw new ValidationErr();

$User = User::byAccessToken()
or throw new AuthErr('User does not exist');

$editableUser = User::byId($id);
$editableUser->powers = $_POST['powers'];
//$editableUser->putToDB();

Response::success();