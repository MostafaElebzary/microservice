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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],


    'auth' => [
        'url' => env('AUTH_SERVICE_URL', 'http://auth-service'),
        'timeout' => env('AUTH_SERVICE_TIMEOUT', 5),
        'retry_times' => env('AUTH_SERVICE_RETRY_TIMES', 3),
        'retry_sleep' => env('AUTH_SERVICE_RETRY_SLEEP', 100),
    ],

    'internal_key' => env('INTERNAL_SERVICE_KEY', 'test'),

];
