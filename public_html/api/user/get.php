<?php

use App\Models\User;
use Symphograph\Bicycle\Api\Response;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Errors\ValidationErr;

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';
$id = $_POST['id'] ?? false
or throw new ValidationErr('id is Empty');

$User = User::byAccessToken() or throw new AppErr('User does not exist');

Response::data($User);