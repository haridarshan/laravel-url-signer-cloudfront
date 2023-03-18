# Laravel CloudFront Url Signer
This a simple wrapper around the official AWS PHP Laravel SDK to generate CloudFront signed URLs


# Installation
The package can be installed via Composer:

```php
composer require haridarshan/laravel-cloudfront-url-signer
```

# Configuration
Publish the config (Optional)

```php
php artisan vendor:publish --provider="Haridarshan\Laravel\CloudFrontUrlSigner\CloudFrontUrlSignerServiceProvider"
```

# Usage

## Signing CloudFront URLs for private distributions

### With Default configuration 
```php
$url = config('filesystems.disks.s3.url') . '/example.mp4';

// Signed CloudFront URL with 1 day expiry
echo CloudFrontUrlSignerFacade::signedUrl($url);
```

### With custom expiry
```php
$url = config('filesystems.disks.s3.url') . '/example.mp4';
$expiry = 60 * 60; // Optional in seconds (Default: 1 day)

// Signed CloudFront URL with 1 hour expiry
echo CloudFrontUrlSignerFacade::signedUrl(
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
echo CloudFrontUrlSignerFacade::signedUrl(
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
result = CloudFrontUrlSignerFacade::signedCookie($url);

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
$result = CloudFrontUrlSignerFacade::signedUrl(
    null,
    null,
    $policy
);
```