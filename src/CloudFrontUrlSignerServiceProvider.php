<?php

namespace Haridarshan\Laravel\CloudFrontUrlSigner;

use Aws\CloudFront\CloudFrontClient;
use Aws\Laravel\AwsServiceProvider;
use Aws\Laravel\AwsFacade;
use RuntimeException;

class CloudFrontUrlSignerServiceProvider extends AwsServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('cloudfront-url-signer.php'),
        ], 'config');
    }

    /**
     * @return void
     */
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'cloudfront-url-signer');

        $this->app->singleton('cloudfront-url-signer', function ($app) {
            if (config('cloudfront-url-signer.key_pair_id') === '') {
                throw new RuntimeException('Key pair id cannot be empty');
            }

            if (config('cloudfront-url-signer.private_key_path') === '') {
                throw new RuntimeException('private key path cannot be empty');
            }

            return new CloudFrontUrlSigner(new CloudFrontClient(config('aws')));
        });

        $this->app->alias('cloudfront-url-signer', CloudFrontUrlSigner::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['cloudfront-url-signer', CloudFrontUrlSigner::class];
    }
}
