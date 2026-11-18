<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'open_meteo' => [
        'url' => env('OPEN_METEO_URL', 'https://api.open-meteo.com/v1/forecast'),
        'timeout' => (int) env('OPEN_METEO_TIMEOUT', 3),
        'api_key' => env('OPEN_METEO_API_KEY', null),
    ],

    'nominatim' => [
        'url' => env('NOMINATIM_URL', 'https://nominatim.openstreetmap.org'),
        'timeout' => (int) env('NOMINATIM_TIMEOUT', 3),
        'user_agent' => env('NOMINATIM_USER_AGENT', 'CargoTrace-Logistics/1.0 (contact@cargotrace.com)'),
        'api_key' => env('NOMINATIM_API_KEY', null),
    ],

];
