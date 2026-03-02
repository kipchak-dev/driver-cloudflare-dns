<?php

use function Kipchak\Core\env;

return [
    'connections' => [
        'default' => [
            'api_token' => env('CF_API_TOKEN', ''),
            // 'base_url' => 'https://api.cloudflare.com/client/v4', // Optional: override the API base URL
        ],
        // Add further named connections for multiple Cloudflare accounts:
        // 'secondary' => [
        //     'api_token' => env('CF_SECONDARY_API_TOKEN', ''),
        // ],
    ],
];
