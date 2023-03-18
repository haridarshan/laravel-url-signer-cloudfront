<?php

namespace Haridarshan\Laravel\UrlSigner\AwsCloudFront\Tests;

use Aws\AwsClientInterface;
use Aws\Sdk;
use Haridarshan\Laravel\UrlSigner\AwsCloudFront\CloudFront;

class CloudFrontTest extends BaseTestCase
{
    /**
     * @test
     */
    public function testAwsClient()
    {
        $client = $this->getAwsClient();

        $this->assertInstanceOf(AwsClientInterface::class, $client);
    }

    /**
     * @test
     */
    public function testClassInstance()
    {
        $client = $this->setupClient();

        $this->assertInstanceOf(CloudFront::class, $client);
    }

    /**
     * @test
     */
    public function testEnsuresKeysArePassed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $c = new CloudFront($this->getAwsClient(), []);

        $c->signedUrl('');
    }

    /**
     * @test
     */
    public function testCloudFrontSignedUrl()
    {
        $client = $this->setupClient();

        $signedUrl = $client->signedUrl('http://example.com');

        $this->assertStringContainsString('Expires', $signedUrl);
        $this->assertStringContainsString('Signature', $signedUrl);
        $this->assertStringContainsString('Key-Pair-Id', $signedUrl);
    }

    /**
     * @test
     */
    public function testCloudFrontSignedUrlExpiresAtCertainTime()
    {
        $client = $this->setupClient();

        // 30 seconds
        $expiry = 30;

        $signedUrl = $client->signedUrl('http://example.com', $expiry);

        $this->assertEquals(
            time() + $expiry,
            $this->getExpiryTime($signedUrl)
        );
    }

    /**
     * @test
     */
    public function testCloudFrontSignedUrlCustomPolicy()
    {
        $client = $this->setupClient();

        $policy = <<<POLICY
{
  "Statement": [
      {
          "Resource": "http://example.com",
          "Condition": {
              "IpAddress": {"AWS:SourceIp": "127.0.0.1/32"},
              "DateLessThan": {"AWS:EpochTime": 3600}
          }
      }
  ]
}
POLICY;

        $signedUrl = $client->signedUrl('http://example.com', null, $policy);

        $this->assertStringContainsString('Policy', $signedUrl);
    }

    /**
     * @test
     */
    public function testCloudFrontSignedCookie()
    {
        $client = $this->setupClient();

        $signedCookies = $client->signedCookie('http://example.com');

        $this->assertArrayHasKey('CloudFront-Expires', $signedCookies);
        $this->assertArrayHasKey('CloudFront-Signature', $signedCookies);
        $this->assertArrayHasKey('CloudFront-Key-Pair-Id', $signedCookies);
    }

    /**
     * @test
     */
    public function testCloudFrontSignedCookieExpiresAtCertainTime()
    {
        $client = $this->setupClient();

        // 30 seconds
        $expiry = 30;

        $signedCookies = $client->signedCookie('http://example.com', $expiry);

        $this->assertArrayHasKey('CloudFront-Expires', $signedCookies);
        $this->assertEquals(
            time() + $expiry,
            $signedCookies['CloudFront-Expires']
        );
    }

    /**
     * @test
     */
    public function testCloudFrontSignedCookieCustomPolicy()
    {
        $client = $this->setupClient();

        $policy = <<<POLICY
{
  "Statement": [
      {
          "Resource": "http://example.com",
          "Condition": {
              "IpAddress": {"AWS:SourceIp": "127.0.0.1/32"},
              "DateLessThan": {"AWS:EpochTime": 3600}
          }
      }
  ]
}
POLICY;

        $signedCookies = $client->signedCookie(null, null, $policy);
        $this->assertArrayHasKey('CloudFront-Policy', $signedCookies);
        $this->assertEquals(
            $this->getCustomPolicy($policy),
            $signedCookies['CloudFront-Policy']
        );
    }

    /**
     * @return CloudFront
     */
    private function setupClient()
    {
        return new CloudFront($this->getAwsClient(), [
            'key_pair_id' => env('CLOUDFRONT_KEY_PAIR_ID'),
            'private_key_path' => env('CLOUDFRONT_PRIVATE_KEY_PATH')
        ]);
    }

    /**
     * @return AwsClientInterface
     */
    private function getAwsClient()
    {
        return (new Sdk([
            'region'  => 'ap-southeast-1',
            'version' => 'latest'
        ]))->createClient('cloudfront');
    }
}