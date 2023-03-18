<?php

namespace Haridarshan\Laravel\UrlSigner\AwsCloudFront;

use Aws\Laravel\AwsFacade;
use Aws\Laravel\AwsServiceProvider;
use RuntimeException;

class CloudFrontServiceProvider extends AwsServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * {@inheritDoc}
     */
    public function boot(): void
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../config/config.php' => get_config_path('cloudfront.php'),
        ], 'cloudfront');
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'cloudfront');

        $this->app->singleton('cloudfront', function () {
            if (get_config('cloudfront.key_pair_id') === '') {
                throw new RuntimeException('Key pair id cannot be empty');
            }

            if (get_config('cloudfront.private_key_path') === '') {
                throw new RuntimeException('private key path cannot be empty');
            }

            return new CloudFront(
                AwsFacade::createClient('cloudfront'),
                get_config('cloudfront')
            );
        });

        $this->app->alias('cloudfront', CloudFront::class);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['cloudfront', CloudFront::class];
    }
}
