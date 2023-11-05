<?php
use Symphograph\Bicycle\Env\Config;
use Symphograph\Bicycle\Env\Server\ServerEnv;
use Symphograph\Bicycle\Errors\Handler;
use Symphograph\Bicycle\Logs\AccessLog;


session_set_cookie_params(0, "/", ServerEnv::SERVER_NAME(), True, True);
Config::redirectFromWWW();
Handler::regHandlers();
Config::initDisplayErrors();
Config::checkPermission();
Config::postHandler();
\App\Env\Config::initEndPoints();
AccessLog::writeToLog();
