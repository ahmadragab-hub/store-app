<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// When Apache rewrites /store-app/... to /store-app/public/..., Laravel would
// otherwise treat "store-app/login" as the route instead of "login".
if (
    PHP_SAPI !== 'cli'
    && isset($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'])
    && str_ends_with($_SERVER['SCRIPT_NAME'], '/public/index.php')
) {
    $publicBase = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '';

    if (! str_starts_with($uriPath, $publicBase.'/')) {
        $_SERVER['SCRIPT_NAME'] = str_replace('/public/index.php', '/index.php', $_SERVER['SCRIPT_NAME']);
        if (isset($_SERVER['PHP_SELF'])) {
            $_SERVER['PHP_SELF'] = str_replace('/public/index.php', '/index.php', $_SERVER['PHP_SELF']);
        }
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
