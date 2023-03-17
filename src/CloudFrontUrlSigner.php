<?php

namespace Haridarshan\Laravel\CloudFrontUrlSigner;

use Aws\CloudFront\CloudFrontClient;

class CloudFrontUrlSigner
{
    /**
     * Aws CloudFront Client
     *
     * @var CloudFrontClient
     */
    protected CloudFrontClient $client;

    /**
     * @param CloudFrontClient $client
     */
    public function __construct(CloudFrontClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get a secure URL to a controller action.
     *
     * @param string $url
     * @param int|null $expiration
     * @param string|null $policy
     *
     * @return string
     */
    public function signedUrl(string $url, int $expiration = null, string $policy = null): string
    {
        return $this->client->getSignedUrl([
            'url' => $url,
            'expires' => $expiration ?? config('cloudfront-url-signer.default_expiration_time_in_seconds'),
            'private_key' => realpath(config('cloudfront-url-signer.private_key_path')),
            'key_pair_id' => config('cloudfront-url-signer.key_pair_id'),
            'policy' => $policy
        ]);
    }

    /**
     * Create a signed Amazon CloudFront cookie.
     *
     * @param string|null $url
     * @param int|null $expiration
     * @param string|null $policy
     *
     * @return array
     */
    public function signedCookie(string $url = null, int $expiration = null, string $policy = null): array
    {
        return $this->client->getSignedCookie([
            'url' => $url,
            'expires' => $expiration ?? config('cloudfront-url-signer.default_expiration_time_in_seconds'),
            'private_key' => realpath(config('cloudfront-url-signer.private_key_path')),
            'key_pair_id' => config('cloudfront-url-signer.key_pair_id'),
            'policy' => $policy
        ]);
    }
}
