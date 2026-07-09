<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// base_path() වෙනුවට සාමාන්‍ය PHP ක්‍රමය මඟින් Root path එක ලබා ගැනීම
$caBundlePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cacert.pem';

if (is_file($caBundlePath)) {
    ini_set('curl.cainfo', $caBundlePath);
    ini_set('openssl.cafile', $caBundlePath);
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();