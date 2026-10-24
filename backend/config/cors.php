<?php

return [
    'paths' => ['api/*', 'api/documentation*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000'))
    ))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'Origin'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => false,
];
