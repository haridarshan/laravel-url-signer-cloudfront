<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Expiration Time
    |--------------------------------------------------------------------------
    |
    | The default expiration time of a URL in seconds.
    |
    */
    'default_expiration_time_in_seconds' => 60 * 60 * 24,

    /*
    |--------------------------------------------------------------------------
    | Trusted Key Group Private Key
    |--------------------------------------------------------------------------
    |
    | The private key used to sign CloudFront URLs.
    |
    */
    'private_key_path' => get_base_path(env('CLOUDFRONT_PRIVATE_KEY_PATH', '')),

    /*
    |--------------------------------------------------------------------------
    | CloudFront Key Pair Id
    |--------------------------------------------------------------------------
    |
    | CloudFront key pair associated to the trusted key groups which validates signed URL
    |
    */
    'key_pair_id' => env('CLOUDFRONT_KEY_PAIR_ID', ''),
];
