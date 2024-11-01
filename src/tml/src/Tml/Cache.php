<?php

/**
 * Copyright (c) 2017 Translation Exchange, Inc. https://translationexchange.com
 *
 *  _______                  _       _   _             ______          _
 * |__   __|                | |     | | (_)           |  ____|        | |
 *    | |_ __ __ _ _ __  ___| | __ _| |_ _  ___  _ __ | |__  __  _____| |__   __ _ _ __   __ _  ___
 *    | | '__/ _` | '_ \/ __| |/ _` | __| |/ _ \| '_ \|  __| \ \/ / __| '_ \ / _` | '_ \ / _` |/ _ \
 *    | | | | (_| | | | \__ \ | (_| | |_| | (_) | | | | |____ >  < (__| | | | (_| | | | | (_| |  __/
 *    |_|_|  \__,_|_| |_|___/_|\__,_|\__|_|\___/|_| |_|______/_/\_\___|_| |_|\__,_|_| |_|\__, |\___|
 *                                                                                        __/ |
 *                                                                                       |___/
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Tml;

/**
 * Class Cache
 *
 * Convenience cache wrapper for exposing the cache interface:
 *
 * Cache::fetch, Cache::store, etc...
 *
 * @package Tml
 */
class Cache {

    private static $instance = null;

    private static $version = null;

    /**
     * Returns an adapter by name
     *
     * @return null|string
     */
    public static function cacheAdapterClass() {
        $adapter = Config::instance()->configValue("cache.adapter");

        switch($adapter) {
            case "file": return '\Tml\Cache\FileAdapter';
            case "apc": return '\Tml\Cache\ApcAdapter';
            case "memcache": return '\Tml\Cache\MemcacheAdapter';
            case "memcached": return '\Tml\Cache\MemcachedAdapter';
            case "redis": return '\Tml\Cache\RedisAdapter';
        }

        return null;
    }

    /**
     * @return \Tml\Cache\Base
     */
    public static function instance() {
        if (self::$instance === null) {
            $class = self::cacheAdapterClass();
            if ($class) self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     * @param string $key
     * @param null $default
     * @return null
     */
    public static function fetch($key, $default = null) {
        if (!Config::instance()->isCacheEnabled()) {
            if (is_callable($default)) {
                return $default();
            }
            return $default;
        }
        return self::instance()->fetch(self::version()->getVersionedKey($key), $default);
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public static function store($key, $value) {
        if (!Config::instance()->isCacheEnabled()) {
            return false;
        }
        return self::instance()->store(self::version()->getVersionedKey($key), $value);
    }

    /**
     * @param $key
     * @return bool
     */
    public static function delete($key) {
        if (!Config::instance()->isCacheEnabled()) {
            return false;
        }
        return self::instance()->delete(self::version()->getVersionedKey($key));
    }

    /**
     * @param $key
     * @return bool
     */
    public static function exists($key) {
        if (!Config::instance()->isCacheEnabled()) {
            return false;
        }
        return self::instance()->exists(self::version()->getVersionedKey($key));
    }

    /**
     * @return bool
     */
    public static function isCachedBySource() {
        if (!Config::instance()->isCacheEnabled() || self::instance() == null)
            return true;
        return self::instance()->isCachedBySource();
    }

    /**
     * @return bool
     */
    public static function isReadOnly() {
        $adapter = Config::instance()->configValue("cache.adapter");
        return $adapter == "file";
    }

    /**
     * Returns cache version
     *
     * @return null|Cache\Version
     */
    public static function version() {
        if (!Config::instance()->isCacheEnabled() || self::instance() == null)
            return null;

        if (self::$version === null) {
            self::$version = new Cache\Version(self::instance());
        }
        return self::$version;
    }

}
