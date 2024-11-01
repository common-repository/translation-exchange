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

use Tml\Utils\ArrayUtils;
use Tml\Utils\StringUtils;

class Config {
    /**
     * @var string[]
     */
    public $config;

    /**
     * @var string
     */
    public $default_locale;

    /**
     * @var int
     */
    public $default_level;

    /**
     * @var string[]
     */
    public $default_tokens;

    /**
     * @var Language
     */
    public $default_language;

    /**
     * @param $config
     */
    public static function init($config) {
        /** @noinspection PhpUnusedLocalVariableInspection */
        static $inst = null;
        /** @noinspection PhpUnusedLocalVariableInspection */
        $inst = $config;
    }

    /**
     * @return Config
     */
    public static function instance() {
        static $inst = null;
        if ($inst === null) {
            $inst = new Config();
        }
        return $inst;
    }

    /**
     *
     */
    function __construct() {
        $this->default_locale = 'en';
        $this->default_level = 0;
    }

    /**
     * @param $configurator
     */
    public static function configure($configurator) {
        $configurator(self::instance());
    }

    /**
     * @return array|mixed|\string[]
     */
    public function configData() {
        if ($this->config == null) {
            $config_file_path = $this->configFilePath('defaults.json');
            $data = file_get_contents($config_file_path);
            $this->config = json_decode($data, true);
        }

        return $this->config;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null|string|\string[]
     */
    public function configValue($key, $default = null) {
        $value = $this->configData();
        $value = ArrayUtils::getAttribute($value, explode(".", $key));
        if ($value === null) return $default;
        return $value;
    }

    /**
     * Sets config value
     *
     * @param $key
     * @param $value
     */
    public function setConfigValue($key, $value) {
        ArrayUtils::createAttribute($this->config, explode(".", $key), $value);
    }

    /**
     * Dumps config back to the config file
     */
    public function dump() {
        file_put_contents($this->configFilePath('config.json'), StringUtils::prettyPrint(json_encode($this->config)));
    }

    /**
     * @return mixed|null|string|\string[]
     */
    public function isLoggerEnabled() {
        return $this->configValue("log.enabled");
    }

    /**
     * @return string
     */
    public function loggerFilePath() {
        $path = $this->configValue("log.path");
        if ($path !== null) return $path;
        return __DIR__."/../../log/tml.log";
    }

    /**
     * @return string
     */
    public function rootPath() {
        return realpath(__DIR__."/../..");
    }

    /**
     * @return int
     */
    public function loggerSeverity() {
        $severity = $this->configValue("log.severity");
        if ($severity == null)
            $severity = "debug";

        if ($severity == "error")
            return Logger::ERROR;

        if ($severity == "warning")
            return Logger::WARNING;

        if ($severity == "notice")
            return Logger::NOTICE;

        if ($severity == "info")
            return Logger::INFO;

        return Logger::DEBUG;
    }

    /**
     * Updates default settings
     *
     * @param $key
     * @param $options
     */
    public function updateConfig($key, $options) {
        $this->configData();
        $this->config[$key] = $options;
    }

    /**
     * @return bool
     */
    public function isCacheEnabled() {
        if ($this->configValue("cache.enabled") === null || $this->configValue("cache.enabled") === false) {
            return false;
        }
        return true;
    }

    /**
     * Disables cache
     */
    public function disableCache() {
        $this->config["cache"]["enable"] = false;
    }

    /**
     * @return string
     */
    public function decoratorClass() {
        $decorator = $this->configValue("decorator");
        if (!$decorator) $decorator = "html";

        if ($decorator == 'plain')
            return '\Tml\Decorators\PlainDecorator';

        return '\Tml\Decorators\HtmlDecorator';
    }

    /**
     * @return mixed
     */
    public function defaultSource() {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * Checks if the locale is valid
     *
     * @param $locale
     * @return int
     */
    public function isValidLocale($locale) {
        if ($locale == null) return false;
        return (preg_match('/^[a-z]{2}(-[A-Z]{2,3})?$/', $locale) == 1);
    }
    /**
     * If SDK is not initialized, we can use the fallback, default language to process TML
     *
     * @return Language
     */
    public function defaultLanguage() {
        if ($this->default_language == null){
            if ($this->default_locale) $this->default_locale = 'en';
            $data = file_get_contents($this->configFilePath('languages/' . $this->default_locale . '.json'));
            $this->default_language = new Language(json_decode($data, true));
        }
        return $this->default_language;
    }

    /**
     * @param $file_name
     * @return string
     */
    public function configFilePath($file_name) {
        return __DIR__."/../../config/" . $file_name;
    }

    /**
     * @param string $key
     * @param string $type
     * @param string $format
     * @return null
     */
    public function defaultToken($key, $type = 'data', $format = 'html') {
        if ($this->default_tokens == null) {
            $data = file_get_contents($this->configFilePath('tokens.json'));
            $this->default_tokens = json_decode($data, true);
        }

        if (!isset($this->default_tokens[$type]))
            return null;

        if (!isset($this->default_tokens[$type][$format]))
            return null;

        if (!isset($this->default_tokens[$type][$format][$key]))
            return null;

        return $this->default_tokens[$type][$format][$key];
    }


    /**
     * @param string $key
     * @param string $value
     * @param string $type
     * @param string $format
     * @return string
     */
    public function setDefaultToken($key, $value, $type = 'data', $format = 'html') {
        if ($this->default_tokens == null) {
            $data = file_get_contents($this->configFilePath('tokens.json'));
            $this->default_tokens = json_decode($data, true);
        }

        if (!isset($this->default_tokens[$type])) {
            $this->default_tokens[$type] = array();
        }

        if (!isset($this->default_tokens[$type][$format])) {
            $this->default_tokens[$type][$format] = array();
        }

        $this->default_tokens[$type][$format][$key] = $value;

        return $this->default_tokens[$type][$format][$key];
    }

    /**
     * @return array
     */
    public function contextRules() {
        return array(
            "number" => array(
                "variables" => array()                      // if mapping is not setup, use the actual object as value
            ),
            "gender" => array(
                "variables" => array(
                    "@gender" => "gender",                  // string means attribute of an object
//                    "@gender" => function($obj) {
//                        return $obj->gender;
//                    }
                )
            ),
            "genders" => array(
                "variables" => array(
                    "@genders" => function($list){
                        $genders = array();
                        foreach($list as $obj) {
                           array_push($genders, $obj->gender);
                        }
                        return $genders;
                    }
                )
            ),
            "date" => array(
                "variables" => array(
                )
            ),
            "time" => array(
                "variables" => array(
                )
            ),
            "list" => array(
                "variables" => array(
                    "@count" => function($list){
                        return count($list);
                    }
              )
          )
        );
    }

    /**
     * @return array
     */
    public function supportedGenders() {
        return array("male", "female", "unknown", "neutral");
    }

    /**
     * @param $input
     * @return string
     */
    protected function base64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * @param $input
     * @return mixed|string
     */
    protected function base64UrlEncode($input) {
        $str = strtr(base64_encode($input), '+/', '-_');
        $str = str_replace('=', '', $str);
        return $str;
    }

    /**
     * @param array $params
     * @return string
     */
    public function encode($params) {
        $data = json_encode($params);
        $payload_json = base64_encode($data);
        return $payload_json;
//        $request = $payload_json;
//        $request = urlencode($payload_json);
//        return $request;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function decode($request) {
        $request = urldecode($request);
        $payload_json = base64_decode($request);
        $data = json_decode($payload_json, true);
        return $data;
    }

    /**
     * @param $signed_request
     * @param $secret
     * @return mixed
     * @throws TmlException
     */
    public function decodeAndVerifyParams($signed_request, $secret) {
        $signed_request = urldecode($signed_request);
        $signed_request = base64_decode($signed_request);

        $parts = explode('.', $signed_request);
        $payload_encoded_sig = trim($parts[0], "\n");
        $payload_json_encoded = $parts[1];

        $verification_sig = hash_hmac('sha256', $payload_json_encoded , $secret, true);
        $verification_sig = trim(base64_encode($verification_sig), "\n");

        if ($payload_encoded_sig != $verification_sig) {
            throw new TmlException("Invalid signature provided.");
        }

        $payload_json = base64_decode($payload_json_encoded);
        $data = json_decode($payload_json, true);
        return $data;
    }

    /**
     * Includes Tml JavaScript library
     */
    public function scripts() {
        include(__DIR__ . '/Includes/AgentScripts.php');
    }

    /**
     * Includes Tml footer scripts
     */
    public function footer() {
        include(__DIR__ . '/Includes/FooterScripts.php');
    }

}
