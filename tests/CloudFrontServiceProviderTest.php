<?php

namespace Haridarshan\Laravel\UrlSigner\AwsCloudFront\Tests;

use Haridarshan\Laravel\UrlSigner\AwsCloudFront\CloudFront;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Haridarshan\Laravel\UrlSigner\AwsCloudFront\CloudFrontServiceProvider;
use Haridarshan\Laravel\UrlSigner\AwsCloudFront\Facades\CloudFrontFacade;

class CloudFrontServiceProviderTest extends BaseTestCase
{
    /**
     * @test
     */
    public function testFacadeCanBeResolvedToServiceInstance()
    {
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);

        // Mount facades
        CloudFrontFacade::setFacadeApplication($app);

        // Get an instance of a client (S3) via the facade.
        $cloudFrontClient = CloudFrontFacade::getClient();
        $this->assertInstanceOf('Aws\CloudFront\CloudFrontClient', $cloudFrontClient);
    }

    /**
     * @test
     */
    public function testRegisterAwsServiceProviderWithPackageConfigAndEnv()
    {
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);

        // Get an instance of a client (CloudFrontClient).
        $cloudFrontClient = $app['cloudfront']->getClient();
        $this->assertInstanceOf('Aws\CloudFront\CloudFrontClient', $cloudFrontClient);

        // Verify that the client received the credentials from the package config.
        $credentials = $cloudFrontClient->getCredentials()->wait();

        $this->assertEquals('foo', $credentials->getAccessKeyId());
        $this->assertEquals('bar', $credentials->getSecretKey());
        $this->assertEquals('baz', $cloudFrontClient->getRegion());
    }

    /**
     * @test
     */
    public function testPrivateKeyPath()
    {
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);
        $config = $app['config']->get('cloudfront');

        $this->assertArrayHasKey('private_key_path', $config);
        $this->assertArrayHasKey('key_pair_id', $config);

        $expectedPrivateKeyPath = get_base_path('tests/test-key.pem');

        $this->assertFileEquals($expectedPrivateKeyPath, $config['private_key_path']);
        $this->assertEquals('testKeyPairId', $config['key_pair_id']);
    }

    /**
     * @test
     */
    public function testCloudFrontServiceInstance()
    {
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);

        $this->assertInstanceOf(CloudFront::class, $app['cloudfront']);
    }

    /**
     * @test
     */
    public function testServiceNameIsProvided()
    {
        $app = $this->setupApplication();
        $provider = $this->setupServiceProvider($app);
        $this->assertContains('cloudfront', $provider->provides());
        $expectArray = ['cloudfront', CloudFront::class];

        $this->assertEquals($expectArray, $provider->provides());
    }

    /**
     * @test
     */
    public function testCloudFrontSignedUrl()
    {
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);

        $signedUrl = CloudFrontFacade::signedUrl('http://example.com');

        $this->assertStringContainsString('Expires', $signedUrl);
        $this->assertStringContainsString('Signature', $signedUrl);
        $this->assertStringContainsString('Key-Pair-Id', $signedUrl);
    }

    /**
     * @test
     */
    public function testCloudFrontSignedUrlExpiresAtCertainTime()
    {
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);

        // 30 seconds
        $expiry = 30;

        $signedUrl = CloudFrontFacade::signedUrl('http://example.com', $expiry);

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
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);

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

        $signedUrl = CloudFrontFacade::signedUrl('http://example.com', null, $policy);

        $this->assertStringContainsString('Policy', $signedUrl);
    }

    /**
     * @test
     */
    public function testCloudFrontSignedCookie()
    {
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);

        $signedCookies = CloudFrontFacade::signedCookie('http://example.com');

        $this->assertArrayHasKey('CloudFront-Expires', $signedCookies);
        $this->assertArrayHasKey('CloudFront-Signature', $signedCookies);
        $this->assertArrayHasKey('CloudFront-Key-Pair-Id', $signedCookies);
    }

    /**
     * @test
     */
    public function testCloudFrontSignedCookieExpiresAtCertainTime()
    {
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);

        // 30 seconds
        $expiry = 30;

        $signedCookies = CloudFrontFacade::signedCookie('http://example.com', $expiry);

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
        $app = $this->setupApplication();
        $this->setupServiceProvider($app);

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

        $signedCookies = CloudFrontFacade::signedCookie(null, null, $policy);
        $this->assertArrayHasKey('CloudFront-Policy', $signedCookies);
        $this->assertEquals(
            $this->getCustomPolicy($policy),
            $signedCookies['CloudFront-Policy']
        );
    }

    /**
     * @return Application
     */
    protected function setupApplication()
    {
        if (!class_exists(Application::class)) {
            $this->markTestSkipped();
        }
        // Create the application such that the config is loaded.
        $app = new Application();
        $app->setBasePath(dirname(__DIR__));
        $app->instance('config', new Repository());

        return $app;
    }

    /**
     * @param Container $app
     *
     * @return CloudFrontServiceProvider
     */
    private function setupServiceProvider(Container $app)
    {
        // Create and register the provider.
        $provider = new CloudFrontServiceProvider($app);
        $app->register($provider);
        $provider->boot();

        return $provider;
    }
}
