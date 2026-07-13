<?php

$frontendOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', env('FRONTEND_URLS', env('FRONTEND_URL', 'http://localhost:3000')))
)));

$frontendOriginPatterns = array_values(array_filter(array_map(static function (string $origin): string {
    $escapedOrigin = preg_quote(rtrim($origin, '/'), '/');

    return '^' . $escapedOrigin . '$';
}, $frontendOrigins)));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => $frontendOrigins,
    'allowed_origins_patterns' => $frontendOriginPatterns,
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
