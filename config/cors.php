<?php
return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    // 🚨 Replace '*' with your frontend URL:
    'allowed_origins' => [
     'http://localhost:5173',
     'http://localhost:8080',
     'http://13.60.6.30:9001',
     'http://localhost:9001'
     ],


    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // 🚨 Must be true to allow cookies!
    'supports_credentials' => true,
    
    
];



