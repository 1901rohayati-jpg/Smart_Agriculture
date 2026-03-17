<?php

return [
    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'database_url' => rtrim(env('FIREBASE_DATABASE_URL', ''), '/'),
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_token' => env('FIREBASE_AUTH_TOKEN'),
        'timeout' => (int) env('FIREBASE_TIMEOUT', 10),
    ],
];
