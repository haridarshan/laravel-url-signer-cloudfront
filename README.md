# Laravel CloudFront Url Signer
This a simple wrapper around the official AWS PHP Laravel SDK to generate CloudFront signed URLs


# Installation
The package can be installed via Composer:

```php
composer require haridarshan/laravel-url-signer-cloudfront
```

# Configuration

## With Laravel
By default, the package uses the following environment variables to auto-configure the plugin without modification:

```dotenv
AWS_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY
AWS_REGION
```
To customize the configuration file, publish the package configuration using Artisan.

```php
php artisan vendor:publish --provider="Haridarshan\Laravel\UrlSigner\AwsCloudFront\CloudFrontServiceProvider"
```

The settings can be found in the generated `config/aws.php` and `config/cloudfront.php` configuration file. By default, the credentials and region settings will pull from your `.env` file.

#### config/aws.php (published by Aws\Laravel\AwsServiceProvider)
```php
return [
    'credentials' => [
        'key'    => env('AWS_ACCESS_KEY_ID', ''),
        'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
    ],
    'region' => env('AWS_REGION', 'us-east-1'),
    'version' => 'latest',
    
    // You can override settings for specific services
    'Ses' => [
        'region' => 'us-east-1',
    ],
];
```

#### config/cloudfront.php
```php
return [
    'default_expiration_time_in_seconds' => 60 * 60 * 24,
    'private_key_path' => get_base_path(env('CLOUDFRONT_PRIVATE_KEY_PATH', '')),
    'key_pair_id' => env('CLOUDFRONT_KEY_PAIR_ID', ''),
];
```

> **Please Note**: Add `CLOUDFRONT_PRIVATE_KEY_PATH` and `CLOUDFRONT_KEY_PAIR_ID` in `.env` file

# Usage

## Signing CloudFront URLs for private distributions

### With Default configuration 
```php
$url = config('filesystems.disks.s3.url') . '/example.mp4';

// Signed CloudFront URL with 1 day expiry
echo \Haridarshan\Laravel\UrlSigner\AwsCloudFront\Facades\CloudFrontFacade::signedUrl($url);
```

### With custom expiry
```php
$url = config('filesystems.disks.s3.url') . '/example.mp4';
$expiry = 60 * 60; // Optional in seconds (Default: 1 day)

// Signed CloudFront URL with 1 hour expiry
echo \Haridarshan\Laravel\UrlSigner\AwsCloudFront\Facades\CloudFrontFacade::signedUrl(
    $url,
    $expiry
);
```

### Use a custom policy to create CloudFront URLs
```php
$url = config('filesystems.disks.s3.url') . '/example.mp4';
$policy = <<<POLICY
{
  "Statement": [
      {
          "Resource": "{$url}",
          "Condition": {
              "IpAddress": {"AWS:SourceIp": "{$_SERVER['REMOTE_ADDR']}/32"},
              "DateLessThan": {"AWS:EpochTime": 3600}
          }
      }
  ]
}
POLICY;

// Signed CloudFront URL with custom policy 
echo \Haridarshan\Laravel\UrlSigner\AwsCloudFront\Facades\CloudFrontFacade::signedUrl(
    $url,
    null,
    $policy
);
```

## Signing CloudFront cookies for private distributions

### With Default configuration 

```php
$url = config('filesystems.disks.s3.url') . '/example.mp4';

// CloudFront Signed Cookies with 1 day expiry
result = \Haridarshan\Laravel\UrlSigner\AwsCloudFront\Facades\CloudFrontFacade::signedCookie($url);

/* If successful, returns something like:
CloudFront-Expires = 1589926678
CloudFront-Signature = Lv1DyC2q...2HPXaQ__
CloudFront-Key-Pair-Id = AAPKAJIKZATYYYEXAMPLE
*/
foreach($result as $key => $value) {
    echo $key . ' = ' . $value . "\n";
}
```

### Use a custom policy to create CloudFront cookies
```php
$url = config('filesystems.disks.s3.url') . '/example.mp4';
$policy = <<<POLICY
{
  "Statement": [
      {
          "Resource": "{$url}",
          "Condition": {
              "IpAddress": {"AWS:SourceIp": "{$_SERVER['REMOTE_ADDR']}/32"},
              "DateLessThan": {"AWS:EpochTime": 3600}
          }
      }
  ]
}
POLICY;

// CloudFront Signed Cookies with custom policy 
$result = \Haridarshan\Laravel\UrlSigner\AwsCloudFront\Facades\CloudFrontFacade::signedUrl(
    null,
    null,
    $policy
);
```

## Outside Laravel Application

```php
require_once "vendor/autoload.php";

$url = "https://example.cloudfront.net/test.mp4";

Dotenv::create(
    Env::getRepository(),
    get_base_path(),
    '.env'
)->safeLoad();

$cloudfront = new \Haridarshan\Laravel\UrlSigner\CloudFront(
    (new Sdk([
        'credentials' => [
            'key'    => env('AWS_ACCESS_KEY_ID', ''),
            'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
        ],
        'region' => env('AWS_REGION', ''),
        'version' => 'latest',
    ]))->createClient('cloudfront'),
    [
        'key_pair_id' => env('CLOUDFRONT_KEY_PAIR_ID', ''),
        'private_key_path' => env('CLOUDFRONT_PRIVATE_KEY_PATH', '')
    ]
);

$signedUrl = $cloudfront->signedUrl($url);
```