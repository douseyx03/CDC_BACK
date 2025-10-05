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

    'sendtext' => [
        'base_url' => env('SENDTEXT_BASE_URL', 'https://api.sendtext.sn'),
        'api_key' => env('SENDTEXT_API_KEY'),
        'api_secret' => env('SENDTEXT_API_SECRET'),
        'sender_name' => env('SENDTEXT_SENDER_NAME', 'CDC'),
        'otp_endpoint' => env('SENDTEXT_OTP_ENDPOINT', '/v1/sms'),
        'otp_template' => env('SENDTEXT_OTP_TEMPLATE', 'Votre code OTP pour CDC est : :code'),
        'message_type' => env('SENDTEXT_MESSAGE_TYPE', 'normal'),
        'timeout' => env('SENDTEXT_TIMEOUT', 10),
    ],

];
