<?php

namespace Haridarshan\Laravel\UrlSigner\AwsCloudFront;

use Aws\AwsClientInterface;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

class CloudFront
{
    /**
     * Aws CloudFront Client
     */
    protected AwsClientInterface $client;

    /**
     * Aws CloudFront Trusted Key Group Private Key Path
     */
    private string $privateKeyPath;

    /**
     * Aws CloudFront Key Pair Id
     */
    private string $keyPairId;

    /**
     * CloudFrontUrlSigner Constructor.
     *
     * Accepts arguments:
     * - client: (AwsClientInterface)  CloudFrontClient
     * - options: (array)
     *       - private_key_path: (string) Path of Trusted Key Group Private Key
     *       - key_pair_id: (string) CloudFront Key Pair Id
     */
    public function __construct(AwsClientInterface $client, array $options)
    {
        $this->client = $client;

        foreach (['key_pair_id', 'private_key_path'] as $required) {
            if (! isset($options[$required])) {
                throw new InvalidArgumentException("$required is required");
            }
        }

        $this->privateKeyPath = realpath($options['private_key_path']);
        $this->keyPairId = $options['key_pair_id'];
    }

    /**
     * Get a secure URL to a controller action.
     *
     * This method accepts following arguments:
     *
     * - url: (string)  URL of the resource being signed (can include query
     *   string and wildcards). For example: rtmp://s5c39gqb8ow64r.cloudfront.net/videos/mp3_name.mp3
     *   http://d111111abcdef8.cloudfront.net/images/horizon.jpg?size=large&license=yes
     * - expires: (int) UTC Unix timestamp used when signing with a canned
     *   policy. Not required when passing a custom 'policy' option. Default 1 day
     * - policy: (string) JSON policy. Use this option when creating a signed
     *   URL for a custom policy.
     *
     * @param  DateTimeInterface|int|null  $expiry
     */
    public function signedUrl(
        string $url,
        $expiry = null,
        string $policy = null
    ): string {
        return $this->client->getSignedUrl([
            'url' => $url,
            'expires' => $this->getTimestamp(
                $expiry ?? get_config(
                    'cloudfront.default_expiration_time_in_seconds',
                    60 * 60 * 24
                )
            ),
            'private_key' => $this->privateKeyPath,
            'key_pair_id' => $this->keyPairId,
            'policy' => $policy,
        ]);
    }

    /**
     * @param  DateTimeInterface|int  $expiry
     */
    protected function getTimestamp($expiry): int
    {
        if (is_int($expiry)) {
            return time() + $expiry;
        }

        if (! $expiry instanceof DateTimeInterface) {
            throw new InvalidArgumentException('Expiry time must be an instance of DateTimeInterface or an integer');
        }

        if (! $this->isFuture($expiry->getTimestamp())) {
            throw new InvalidArgumentException('Expiry time must be in the future');
        }

        return $expiry->getTimestamp();
    }

    /**
     * Check if a timestamp is in the future.
     */
    protected function isFuture(int $timestamp): bool
    {
        return $timestamp >= (new DateTime())->getTimestamp();
    }

    /**
     * Create a signed Amazon CloudFront cookie.
     *
     * This method accepts following arguments:
     *
     * - url: (string)  URL of the resource being signed (can include query
     *   string and wildcards). For example: http://d111111abcdef8.cloudfront.net/images/horizon.jpg?size=large&license=yes
     * - expires: (int) UTC Unix timestamp used when signing with a canned
     *   policy. Not required when passing a custom 'policy' option. Default 1 day
     * - policy: (string) JSON policy. Use this option when creating a signed
     *   URL for a custom policy.
     *
     * @param  DateTimeInterface|int|null  $expiry
     */
    public function signedCookie(
        string $url = null,
        $expiry = null,
        string $policy = null
    ): array {
        return $this->client->getSignedCookie([
            'url' => $url,
            'expires' => $this->getTimestamp(
                $expiry ?? get_config(
                    'cloudfront.default_expiration_time_in_seconds',
                    60 * 60 * 24
                )
            ),
            'private_key' => $this->privateKeyPath,
            'key_pair_id' => $this->keyPairId,
            'policy' => $policy,
        ]);
    }

    /**
     * Get AWS CloudFront Client
     */
    public function getClient(): AwsClientInterface
    {
        return $this->client;
    }
}
