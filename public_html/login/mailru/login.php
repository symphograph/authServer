<?php


use App\AuthCallBack;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';


AuthCallBack::loginChecks();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Войти через Mail.ru</title>
    <style>
        .btn:hover {
            box-shadow: 1px 1px 5px bisque;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div style="width: max-content; margin: auto">
    <h4>Войти через</h4>
    <a href="callback.php">
        <div class="btn">
            <img  src="/tauth/img/46ed2594ead7fb40bfe59703e2b1e0b0.png">
        </div>
    </a>
</div>


</body>
</html>
