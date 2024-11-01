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

/**
 * Class Base
 *
 * Base cache client class
 *
 * @package Tml\Cache
 */
abstract class Base {
    public abstract function fetch($key, $default = null);
    public abstract function store($key, $value);
    public abstract function delete($key);
    public abstract function exists($key);

    /**
     * @var string
     */
    private $base_cache_path;

    /**
     * @return bool
     */
    public function isCachedBySource() {
        return true;
    }

    /**
     * @return bool
     */
    public function isReadOnly() {
        return false;
    }

    /**
     * @return string
     */
    public function key() {
        $parts = explode('\\', get_class($this));
        return $parts[2];
    }

    /**
     * @param string $msg
     */
    function warn($msg) {
        Logger::instance()->warn($this->key() . " - " . $msg);
    }

    /**
     * @param string $msg
     */
    function info($msg) {
        Logger::instance()->info($this->key() . " - " . $msg);
    }

    /**
     * @return string
     */
    function baseCachePath() {
        if ($this->base_cache_path == null) {
            $this->base_cache_path = Config::instance()->configValue("cache.path");

            if (!$this->base_cache_path) {
                $this->base_cache_path = Config::instance()->rootPath() . DIRECTORY_SEPARATOR . "cache";
            } elseif ($this->base_cache_path[0] != "/") {
                $this->base_cache_path = Config::instance()->rootPath() . DIRECTORY_SEPARATOR . $this->base_cache_path;
            }

            if (!file_exists($this->base_cache_path)) mkdir($this->base_cache_path, 0777, true);
            $this->info("Base cache path: " . $this->base_cache_path);
        }
        return $this->base_cache_path;
    }

    /**
     * @return string
     */
    function currentCachePath() {
        return $this->baseCachePath() . DIRECTORY_SEPARATOR . Config::instance()->configValue("cache.version", 'current');
    }

    /**
     * Removes extensions from data
     *
     * @param $data
     * @return string
     */
    function stripExtensions($data) {
        if (!is_string($data))
            $data = json_encode($data);

        if (!preg_match("/^{/", $data))
            return $data;

        $json = json_decode($data, true);
        if (isset($json["extensions"])) {
            unset($json["extensions"]);
        }
        $json = json_encode($json);

//        $this->info($json);

        return $json;
    }
}
