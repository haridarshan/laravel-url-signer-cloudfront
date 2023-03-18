<?php

namespace Haridarshan\Laravel\CloudFrontUrlSigner\Facades;

use Aws\AwsClientInterface;
use DateTimeInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for the AWS service
 *
 * @method static string signedUrl(string $url, DateTimeInterface|int $expiry = null, string $policy = null) Create a signed Amazon CloudFront URL.
 * @method static array signedCookie(string $url = null, DateTimeInterface|int $expiry = null, string $policy = null) Create a signed Amazon CloudFront cookie.
 * @method static AwsClientInterface getClient() Get CloudFront client
 */
class CloudFrontUrlSignerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cloudfront-url-signer';
    }
}
