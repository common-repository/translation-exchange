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

namespace Tml\Api;

use Tml\CacheVersion;
use Tml\Logger;
use Tml\Config;
use Tml\TmlException;
use Tml\Cache;
use Tml\Version;
use Tml\Session;

class Client {
    const CDN_HOST = 'https://cdn.translationexchange.com';
    const API_HOST = 'https://api.translationexchange.com';
    const API_PATH = '/v1/';

    /**
     * Stores application for which the API client belongs.
     * @var \Tml\Application
     */
    private $application;

    /**
     * Creates API Client
     *
     * @param \Tml\Application $app
     */
    function __construct($app) {
        $this->application = $app;
    }

    /**
     * Curl options
     *
     * @return array
     */
    private static function getOptions() {
        return array(
            CURLOPT_CONNECTTIMEOUT => Config::instance()->configValue('api.connection_timeout', 10),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => Config::instance()->configValue('api.timeout', 60)
        );
    }

    /**
     * Executes an API call using a CURL command
     *
     * @param string $path
     * @param array $params
     * @param array $options
     * @return array
     * @throws TmlException
     */
    public static function executeRequest($path, $params = array(), $options = array()) {
        $t0 = microtime(true);

        $ch = curl_init();

        if (FALSE === $ch) {
            Logger::instance()->error("Failed to initialize curl");
            throw new TmlException('Failed to initialize curl');
        }

        $opts = self::getOptions();
        $opts[CURLOPT_USERAGENT] = "tml-php v" . Version::VERSION . " (CURL)";

        $api_host = isset($options['host']) ? $options['host'] : self::API_HOST;

        if (!isset($options['method']))
            $options['method'] = 'GET';

        if ($options['method'] == 'POST') {
            $opts[CURLOPT_URL] = $api_host . $path;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
            Logger::instance()->info("POST: " . $opts[CURLOPT_URL]);
            Logger::instance()->info("DATA: ", $params);
        } else {
            if (count($params) > 0)
                $opts[CURLOPT_URL] = $api_host . $path . '?' . http_build_query($params, null, '&');
            else
                $opts[CURLOPT_URL] = $api_host . $path;

            Logger::instance()->info("GET: " . $opts[CURLOPT_URL]);
        }

        $opts[CURLOPT_ENCODING] = "gzip, deflate";
        $opts[CURLOPT_VERBOSE] = 0;

        curl_setopt_array($ch, $opts);

        $result = curl_exec($ch);

        if (FALSE === $result) {
            Logger::instance()->error(curl_error($ch) . ": " . curl_errno($ch));
            throw new TmlException("" . curl_error($ch) . ": " . curl_errno($ch));
        }

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);

//        Logger::instance()->info($result);

        if ($http_status < 200 && $http_status > 299) {
            Logger::instance()->error("Got HTTP response: $http_status");
            throw new TmlException("Got HTTP response: $http_status");
        }

        curl_close($ch);

        $t1 = microtime(true);
        $milliseconds = round($t1 - $t0,3)*1000;
        Logger::instance()->info("Received compressed $content_size uncompressed " . strlen($result) . " in " . $milliseconds . " milliseconds");

        return $result;
    }

    /**
     * Fetches data from the CDN based on the current cache version
     *
     * @param $cache_key
     * @param $key
     * @return array
     */
    public function fetchFromCdn($cache_key) {
        if ($cache_key == Cache\Version::TML_VERSION_KEY)
            $cdn_path = "/" . $this->application->key . "/version.json";
        else
            $cdn_path = "/" . $this->application->key . "/" . Cache::version()->version . "/" . $cache_key . ".json";

        $cdn_host = $this->application->cdn_host;
        if (!$cdn_host) $cdn_host = self::CDN_HOST;

        try {
            $data = self::executeRequest($cdn_path, array(), array("host" => $cdn_host, "compressed" => false));
        } catch (\Exception $ex) {
            $data = null;
        }

        // AWS returns XML messages when data is not found
        if (!$data || preg_match("/xml/", $data)) return null;

        return $data;
    }

    /**
     * Processes API response data
     *
     * @param string $data
     * @param array $options
     * @return array
     */
    public static function processResponse($data, $options = array()) {
        if (isset($options['raw_json']))
            return $data;

        if (isset($data['results'])) {
            // Logger::instance()->info("Received " . count($data["results"]) ." result(s)");

            if (!isset($options["class"])) return $data["results"];

            $objects = array();
            foreach($data["results"] as $json) {
                array_push($objects, self::createObject($json, $options));
            }
            return $objects;
        }

        if (!isset($options["class"])) return $data;
        return self::createObject($data, $options);
    }

    /**
     * Creates objects from response data
     *
     * @param $data
     * @param $options
     * @return mixed
     */
    public static function createObject($data, $options) {
        if ($options != null && array_key_exists('attributes', $options)) {
            $data = array_merge($data, $options['attributes']);
        }
        return new $options["class"]($data);
    }


    /*
     * Executes API Get call
     *
     * @param string $path
     * @param array $params
     * @param array $options
     * @return array
     */
    public function get($path, $params = array(), $options = array()) {
        return $this->api($path, $params, $options);
    }

    /**
     * Executes API Post call
     *
     * @param string $path
     * @param array $params
     * @param array $options
     * @return array
     */
    public function post($path, $params = array(), $options = array()) {
        $options["method"] = 'POST';
        return $this->api($path, $params, $options);
    }

    /**
     * Checks of the request is live or from cache
     */
    public function isLiveApiRequest() {
        if ($this->application->access_token == null)
            return false;

        return Session::instance()->isInlineTranslationModeEnabled();
    }

    /**
     * Checks if cache is enabled
     */
    public function isCacheEnabled($options) {
        // only get methods ever need to be cached
        if ($options['method'] != 'GET')
            return false;

        if (!isset($options['cache_key']))
            return false;

        return Config::instance()->isCacheEnabled();
    }

    /**
     * Executes API call
     *
     * @param string $path
     * @param array $params
     * @param array $options
     * @return array
     */
    public function api($path, $params = array(), $options = array()) {
        $options["host"] = $this->application->host ? $this->application->host : self::API_HOST;
        $options["cdn_host"] = $this->application->cdn_host ? $this->application->cdn_host : self::CDN_HOST;

        if (!isset($options['method']))
            $options['method'] = 'GET';

        if ($this->isLiveApiRequest()) {
            $params["access_token"] = $this->application->access_token;
            $params["app_id"] = $this->application->key;
            $data = self::executeRequest(self::API_PATH . $path, $params, $options);
            $json = json_decode($data, true);
            return self::processResponse($json, $options);
        }

        if (!$this->isCacheEnabled($options)) {
            Logger::instance()->debug("Cache is not enabled");
            return null;
        }

        if (Cache::version()->isUndefined())
            Cache::version()->updateFromCdn($this->fetchFromCdn(Cache\Version::TML_VERSION_KEY));

        if (Cache::version()->isUnsupported()) return null;

        $data = Cache::fetch($options["cache_key"]);

        if ($data) {
            $json = json_decode($data, true);
            return self::processResponse($json, $options);
        }

        if (Cache::isReadOnly()) return null;

        $data = $this->fetchFromCdn($options["cache_key"]);

        if ($data === null) return null;

        $json = json_decode($data, true);

        if (isset($json['error'])) return null;

        Cache::store($options["cache_key"], $data);

        return self::processResponse($json, $options);
    }

}