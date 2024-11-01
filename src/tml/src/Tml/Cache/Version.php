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

namespace Tml\Cache;

use Tml\Logger;
use Tml\Config;

class Version {

    /**
     * Cache information is stored under this key
     */
    const TML_VERSION_KEY = 'current_version';

    /**
     * Holds the current cache version
     *
     * @var string
     */
    public $version;

    /**
     * Holds an instance of the cache object
     *
     * @var Base
     */
    private $cache;

    /**
     * Constructs the cache version
     *
     * @param $cache
     */
    public function __construct($cache) {
        $this->cache = $cache;
        $this->fetch();
    }

    /**
     * Resets the internal cache version
     */
    public function reset() {
        $this->version = null;
    }

    /**
     * Sets the current cache version
     *
     * @param $new_version
     */
    public function set($new_version) {
        $this->version = $new_version;
    }

    /**
     * Marks the cache as needed to refresh from CDN
     */
    public function upgrade() {
        $this->cache->store(self::TML_VERSION_KEY, array('version' => 'undefined', 't' => $this->getTimestamp()));
        $this->reset();
    }

    /**
     * Validates cache version
     *
     * @param $version_data
     * @return string
     */
    private function validate($version_data) {
        // If the version is hardcoded in the config, use it
        if (Config::instance()->configValue("cache.version") !== null) {
            return Config::instance()->configValue("cache.version");
        }

        try {
            $version_data = json_decode($version_data, true);
        } catch (Exception $e) {
            $version_data = 'undefined';
        }

        if (!is_array($version_data) || !isset($version_data['t']))
            return 'undefined';

        Logger::instance()->debug("Local cache version " . $version_data['version']);

        if ($this->cache->isReadOnly())
            return $version_data['version'];

        $interval = $this->getVersionCheckInterval();
        if ($interval == -1) {
            Logger::instance()->debug('Cache version check is disabled');
            return $version_data['version'];
        }

        $expires_at = $version_data['t'] + $interval;
        $now = time();
        if ($expires_at < $now) {
            Logger::instance()->debug('Cache version is outdated, needs refresh');
            return 'undefined';
        }

        $delta = $expires_at - $now;
        Logger::instance()->debug('Cache version is up to date, expires in ' . $delta . 's');
        return $version_data['version'];
    }

    /**
     * Fetches cache version data from cache
     */
    public function fetch() {
        if ($this->cache->isReadOnly())
            return;

        $version_data = $this->cache->fetch($this->getVersionedKey(self::TML_VERSION_KEY));
        if ($version_data) {
            $this->version = $this->validate($version_data);
        } else {
            $this->store(Config::instance()->configValue("cache.version", 'undefined'));
        }
        return $this->version;
    }

    /**
     * Stores the new version in memory and cache
     *
     * @param $new_version
     */
    public function store($new_version) {
        $this->version = $new_version;

        Logger::instance()->debug("Storing version in local cache as " . $new_version);

        $this->cache->store($this->getVersionedKey(self::TML_VERSION_KEY), array(
            'version' => $new_version,
            't' => $this->getTimestamp()
        ));
    }

    /**
     * Returns cache versioned key
     *
     * @param $key
     * @return string
     */
    public function getVersionedKey($key) {
        if ($this->cache->isReadOnly())
            return $key;

        return "tml_" .
            Config::instance()->configValue("cache.namespace") .
            ($key == self::TML_VERSION_KEY ? '' : ('_v' . $this->version)) .
            "_" .
            $key;
    }

    /**
     * Checks if current version is undefined
     *
     * @return bool
     */
    function isUndefined() {
        if ($this->cache->isReadOnly()) return false;
        return ($this->version == null || $this->version == 'undefined');
    }

    /**
     * Checks if current version is disabled
     *
     * @return bool
     */
    function isDisabled() {
        return ($this->version == 'disabled');
    }

    /**
     * Checks if current version is unsupported
     *
     * @return bool
     */
    function isUnsupported() {
        if ($this->cache->isReadOnly()) return false;
        return ($this->isUndefined() || $this->isDisabled() || $this->version == 'unsupported');
    }

    /**
     * Returns cache timestamp
     *
     * @return int
     */
    public function getTimestamp() {
        return time();
    }

    /**
     * Gets the time interval for checking the cache version
     *
     * @return mixed|null|string|\string[]
     */
    public function getVersionCheckInterval() {
        return Config::instance()->configValue("cache.version_check_interval", 3600);
    }

    /**
     * Updates version in local cache from CDN data
     *
     * @param $version_data
     */
    public function updateFromCdn($version_data) {
        if ($version_data === null) {
            $version = 'unsupported';
        } else {
            $json = json_decode($version_data, true);
            $version = $json["version"];
        }

        $this->store($version);
    }
}
