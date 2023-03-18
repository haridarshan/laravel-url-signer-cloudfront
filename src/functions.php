<?php

use Illuminate\Config\Repository as ConfigRepository;

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the installation.
     *
     * @param string $path
     *
     * @return string
     */
    function base_path($path = ''): string
    {
        return dirname(__DIR__, 4) . ($path != '' ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (! function_exists('join_path')) {
    /**
     * Join paths
     *
     * @param $basePath
     * @param $path
     *
     * @return string
     */
    function join_path($basePath, $path = '')
    {
        return $basePath . (
            $path != '' ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''
        );
    }
}

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     *
     * @return string
     */
    function config_path($path = '')
    {
        return join_path(base_path('config'), $path);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string|null $key
     * @param mixed|null $default
     *
     * @return mixed|ConfigRepository
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return new ConfigRepository();
        }

        if (is_array($key)) {
            return (new ConfigRepository(require __DIR__ . '/../config/config.php'))->set($key);
        }

        return (new ConfigRepository(require __DIR__ . '/../config/config.php'))->get($key, $default);
    }
}
