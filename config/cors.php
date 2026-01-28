<?php
return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    // 🚨 Replace '*' with your frontend URL:
    'allowed_origins' => [
     'http://localhost:5173',
     'http://localhost:8080', 
     'https://nesti-connect-homepage-dev.vercel.app',
     'https://www.nesticommunity.com'
     ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // 🚨 Must be true to allow cookies!
    'supports_credentials' => true,
    
    
];



