<?php

namespace Haridarshan\Laravel\CloudFrontUrlSigner\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the AWS service
 *
 * @method static string signedUrl(string $url, int $expiration = null, string $policy = null) Create a signed Amazon CloudFront URL.
 * @method static array signedCookie(string $url = null, int $expiration = null, string $policy = null) Create a signed Amazon CloudFront cookie.
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
