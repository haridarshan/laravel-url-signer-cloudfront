<?php

namespace Haridarshan\Laravel\UrlSigner\AwsCloudFront\Tests;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @param $url
     *
     * @return void
     */
    protected function getExpiryTime($url)
    {
        parse_str(parse_url($url)['query'], $queryParams);
        return $queryParams['Expires'];
    }

    /**
     * @param $policy
     *
     * @return string
     */
    protected function getCustomPolicy($policy)
    {
        $policy = preg_replace('/\s/s', '', $policy);
        return strtr(base64_encode($policy), '+=/', '-_~');
    }
}