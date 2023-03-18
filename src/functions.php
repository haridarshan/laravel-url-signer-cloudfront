<?php

use Illuminate\Config\Repository as ConfigRepository;

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

if (! function_exists('get_base_path')) {
    /**
     * Get App Base Path
     *
     * @param $path
     *
     * @return string
     */
    function get_base_path($path = '') {
        if (function_exists('base_path')) {
            return base_path($path);
        } else {
            return dirname(__DIR__, 1) . ($path != '' ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
        }
    }
}

if (! function_exists('get_config_path')) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     *
     * @return string
     */
    function get_config_path($path = '')
    {
        if (function_exists('base_path')) {
            return join_path(base_path('config'), $path);
        } else {
            return join_path(get_base_path('config'), $path);
        }
    }
}

if (! function_exists('get_config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string|null $key
     * @param mixed|null $default
     *
     * @return mixed|ConfigRepository|void
     */
    function get_config($key = null, $default = null)
    {
        if (function_exists('config')) {
            return config($key, $default);
        } else {
            if (is_null($key)) {
                return new ConfigRepository();
            }

            if (is_array($key)) {
                return (new ConfigRepository(require __DIR__ . '/../config/config.php'))->set($key);
            }

            return (new ConfigRepository(require __DIR__ . '/../config/config.php'))->get($key, $default);
        }
    }
}
