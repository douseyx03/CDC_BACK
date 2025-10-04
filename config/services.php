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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    'axiomtext' => [
        'base_url' => env('AXIOMTEXT_BASE_URL', 'https://api.axiomtext.com'),
        'api_key' => env('AXIOMTEXT_API_KEY'),
        'sender_id' => env('AXIOMTEXT_SENDER_ID', 'CDC'),
        'otp_endpoint' => env('AXIOMTEXT_OTP_ENDPOINT', '/api/sms/otp/send'),
        'otp_template' => env('AXIOMTEXT_OTP_TEMPLATE', 'Votre code OTP pour CDC est : :code'),
        'timeout' => env('AXIOMTEXT_TIMEOUT', 10),
    ],

];

