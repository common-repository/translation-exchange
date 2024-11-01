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
use Tml\Utils\BrowserUtils;
use Tml\Tokenizers\DomTokenizer;
use Tml\Utils\StringUtils;
use Tml\Utils\UrlUtils;

class Session
{
    /**
     * @var Application
     */
    public $application;

    /**
     * @var mixed
     */
    public $current_user;

    /**
     * @var string
     */
    public $current_locale;

    /**
     * @var Language
     */
    public $current_language;

    /**
     * @var Translator
     */
    public $current_translator;

    /**
     * @var Source
     */
    public $current_source;

    /**
     * @var array
     */
    public $block_options;

    /**
     * @var array
     */
    public $locale_options;

    /**
     * @return Session
     */
    public static function instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Session();
        }
        return $inst;
    }

    /**
     *
     */
    function __construct()
    {
        $this->key = null;
        $this->application = null;
        $this->current_locale = null;
        $this->current_language = null;
        $this->current_user = null;
        $this->current_translator = null;
        $this->current_source = null;
        $this->block_options = array();
    }

    /**
     * Returns access token
     *
     * @return string
     */
    public function accessToken()
    {
        return $this->application->access_token;
    }

    /**
     * Checks if keys should be sent to the server
     *
     * @return bool
     */
    public function isKeyRegistrationEnabled()
    {
        if (!Config::instance()->isCacheEnabled())
            return true;

        if ($this->isInlineTranslationModeEnabled())
            return true;

        return false;
    }

    /**
     * @param array $options
     */

    public function completeRequest(/** @noinspection PhpUnusedParameterInspection */
        $options = array())
    {
        if (!isset($this->application)) return;
        $this->application->submitMissingKeys();
    }

    /**
     * @param array $options
     */
    public function beginBlockWithOptions($options = array())
    {
        array_push($this->block_options, $options);
    }

    /**
     * @param $key
     * @return null
     */
    public function getBlockOption($key)
    {
        if (count($this->block_options) == 0) return null;
        $current_options = $this->block_options[count($this->block_options) - 1];
        if (!array_key_exists($key, $current_options)) return null;
        return $current_options[$key];
    }

    /**
     * @return null
     */
    public function finishBlockWithOptions()
    {
        if (count($this->block_options) == 0) return null;
        array_pop($this->block_options);
    }

    /**
     * @return bool
     */
    public function isInlineTranslationModeEnabled()
    {
        return ($this->current_translator && $this->current_translator->isInlineModeEnabled());
    }

    /**
     * Initializes the TML request
     *
     * @param array $options
     * @return bool
     */
    static function init($options = array())
    {
        global $tml_page_t0;
        $tml_page_t0 = microtime(true);

        foreach (array("cache", "log", "agent") as $type) {
            if (isset($options[$type]))
                Config::instance()->updateConfig($type, $options[$type]);
        }

        $key = isset($options["key"]) ? $options["key"] : Config::instance()->configValue("application.key");
        $host = isset($options["host"]) ? $options["host"] : Config::instance()->configValue("application.host");
        $cdn_host = isset($options["cdn_host"]) ? $options["cdn_host"] : Config::instance()->configValue("application.cdn_host");

        // get cookie name
        $cookie_name = "trex_" . $key;
        Logger::instance()->debug("Cookie name: " . $cookie_name);

        $translator = null;
        $cookie_params = null;
        $token = null;

        // check if cookie is set
        if (isset($_COOKIE[$cookie_name])) {
            $cookie_params = Config::instance()->decode($_COOKIE[$cookie_name]);
            if (isset($cookie_params['translator'])) {
                $translator = new Translator(array_merge($cookie_params["translator"]));
            }
            if (isset($cookie_params['oauth'])) {
                $token = $cookie_params['oauth']['token'];
            }
        }

        if (!$cookie_params) $cookie_params = array();
        if (!$token)
            $token = isset($options["token"]) ? $options["token"] : Config::instance()->configValue("application.token");

        self::instance()->locale_options = array();
        if (isset($options['locale'])) {
            self::instance()->locale_options = is_array($options['locale']) ? $options['locale'] : array(
                "locale" => $options['locale']
            );
        }
        $requested_locale = self::getRequestedLocale();
        $desired_locale = ($requested_locale == null ? self::getDesiredLocale($cookie_params) : $requested_locale);
        Logger::instance()->debug("Requested Locale: " . $requested_locale . " Desired Locale:" . $desired_locale);

//        Logger::instance()->debug("Cookie params: " . json_encode($cookie_params));
//        Logger::instance()->debug("Options: " . json_encode($options));

        # by default always use the access token of the translator

        // create application instance, but don't initialize it yet
        $application = new Application(array("key" => $key, "access_token" => $token, "host" => $host, "cdn_host" => $cdn_host));

        self::instance()->application = $application;
        self::instance()->current_translator = $translator;
        self::instance()->current_locale = $desired_locale;

        self::instance()->detectSource($options);

        try {
            $application->fetch();
        } catch (\Exception $e) {
            Logger::instance()->error("Application failed to initialize: " . $e);
        }

        $current_locale = $application->supportedLocale($desired_locale);

        if (!self::isActive()) {
            Logger::instance()->debug("Application is not active");
            Session::instance()->current_language = $application->language(Config::instance()->default_locale);
            return false;
        }

        Logger::instance()->debug("Application initialized");
        Logger::instance()->debug("Actual Locale: " . $current_locale);

        Session::instance()->current_language = $application->language($current_locale);
        self::updateLocale($requested_locale, $current_locale, $cookie_name, $cookie_params);
        return true;
    }

    /**
     * Determines current source
     *
     * @param $source
     * @param $url_path
     * @return mixed|string
     */
    function detectSource($options) {
        $url_path = isset($_SERVER["REQUEST_URI"]) ? StringUtils::normalizeSource($_SERVER["REQUEST_URI"]) : 'index';
        $source = isset($options["source"]) ? $options["source"] : null;

        if ($source) {
            if (is_callable($source)) {
                $source = $source($url_path);
            } elseif (ArrayUtils::isHash($source)) {
                $source = StringUtils::matchSource($source, $url_path);
            }
        } else {
            $source = $url_path;
        }

        if (!$source || $source == '' || $source == '/')
            $source = "index";

        $this->current_source = $source;
    }

    /**
     * Updates locale in the cookie or redirects to the appropriate URL based on the strategy
     *
     * @param $requested_locale
     * @param $desired_locale
     * @param $current_locale
     * @param $locale_options
     * @param $cookie_name
     * @param $cookie_params
     * @return bool
     */
    static function updateLocale($requested_locale, $current_locale, $cookie_name, $cookie_params)
    {
        $locale_options = self::instance()->locale_options;
        $strategy = self::localeStrategy();

        # check if we want to store the last selected locale in the cookie
        if ($requested_locale == $current_locale && self::isLocaleCookieEnabled($strategy, $locale_options)) {
            $cookie_params["locale"] = $current_locale;
            setcookie($cookie_name, Config::instance()->encode($cookie_params), null, "/");
        }

        $redirect_enabled = isset($locale_options['redirect']) ? $locale_options['redirect'] : true;
        if (!$redirect_enabled)
            return false;

        $ignore_urls = isset($locale_options['ignore_urls']) ? $locale_options['ignore_urls'] : null;
        if ($ignore_urls != null) {
            if (is_string($ignore_urls))
                $ignore_urls = array($ignore_urls);
            foreach ($ignore_urls as $rule)
                if (preg_match($rule, $_SERVER['REQUEST_URI']) == 1)
                    return false;
        }

        $skip_default = isset($locale_options['skip_default']) ? $locale_options['skip_default'] : true;
        $default_locale = isset($locale_options['default']) ? $locale_options['default'] : Config::instance()->default_locale;
        $default_subdomain = self::localeDefaultSubdomain();
        $mapping = self::localeMapping();

        if ($skip_default && $current_locale == $default_locale) {

            # first lets see if we are in default locale and user doesn't want to show locale in url
            if ($strategy == 'pre-path' && $requested_locale !== null) {
                $path = $_SERVER['REQUEST_URI'];
                $prefix = self::localePathPrefix();

                if ($prefix !== null) {
                    $path = preg_replace('/^' . str_replace('/', '\/', $prefix) . '/', '', $path);
                }

                $fragments = StringUtils::split($path, '/');
                if (count($fragments) > 0 && Config::instance()->isValidLocale($fragments[0])) {
                    array_shift($fragments);
                    UrlUtils::redirect(UrlUtils::urlFor(null, StringUtils::join($fragments, '/')));
                    return true;
                }
                return false;
            }

            if ($strategy == 'pre-domain') {
                $subdomains = StringUtils::split($_SERVER['SERVER_ADDR'], '.');
                if (count($subdomains) > 2 && Config::instance()->isValidLocale($subdomains[0])) {
                    if ($default_subdomain != null) {
                        $subdomains[0] = $default_subdomain;
                    } else {
                        array_shift($subdomains);
                    }
                    UrlUtils::redirect(UrlUtils::urlFor(StringUtils::join($subdomains, '.')));
                    return true;
                }
                return false;
            }

            if ($strategy == 'custom-domain' && isset($mapping[$default_locale])) {
                UrlUtils::redirect(UrlUtils::urlFor($mapping[$default_locale]));
                return true;
            }

            return false;
        }

        if ($requested_locale == $current_locale)
            return false;

        # otherwise, the locale is not the same as what was requested, deal with it
        if ($strategy == 'pre-path') {
            $path = $_SERVER['REQUEST_URI'];
            $prefix = self::localePathPrefix();

            if ($prefix !== null) {
                $path = preg_replace('/^' . str_replace('/', '\/', $prefix) . '/', '', $path);
            }

            $fragments = StringUtils::split($path, '/');
            if (count($fragments) > 0 && Config::instance()->isValidLocale($fragments[0])) {
                $fragments[0] = $current_locale;
            } else {
                array_unshift($fragments, $current_locale);
            }

            if ($prefix !== null) {
                array_unshift($fragments, $prefix);
            }

            UrlUtils::redirect(UrlUtils::urlFor(null, StringUtils::join($fragments, '/')));
            return true;
        }

        if ($strategy == 'pre-domain') {
            $subdomains = StringUtils::split($_SERVER['SERVER_ADDR'], '.');
            if (count($subdomains) > 2 && (Config::instance()->isValidLocale($subdomains[0]) || $subdomains[0] == $default_subdomain))
                $subdomains[0] = $current_locale;
            else
                array_unshift($subdomains, $current_locale);

            UrlUtils::redirect(UrlUtils::urlFor(StringUtils::join($subdomains, '.')));
            return true;
        }

        if ($strategy == 'custom-domain') {
            $host = isset($mapping[$current_locale]) ? $mapping[$current_locale] : (
                isset($mapping[$default_locale]) ? $mapping[$default_locale] : null
            );

            if ($host != null) {
                UrlUtils::redirect(UrlUtils::urlFor($host));
                return true;
            }
        }

        return false;
    }


    /**
     * Return desired locale based on either requested locale, cookie or browser locale
     *
     * @param $locale
     * @param $options
     * @param $cookie_params
     * @return null|string
     */
    static function getDesiredLocale($cookie_params)
    {
        $locale_options = self::instance()->locale_options;

        if (isset($locale_options['locale'])) {
            $locale = $locale_options['locale'];
            if (is_string($locale))
                return $locale;
            if (is_callable($locale))
                return $locale();
        }

        $strategy = self::localeStrategy();

        Logger::instance()->debug("Strategy " . $strategy);

        # check if locale was previously stored in a cookie
        if (self::isLocaleCookieEnabled($strategy, $locale_options) && isset($cookie_params['locale'])) {
            Logger::instance()->debug("Using Cookie");
            return $cookie_params['locale'];
        }

        # fallback onto the browser locale
        if (self::useBrowserHeaderLocale() && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            return BrowserUtils::acceptedLocales($_SERVER['HTTP_ACCEPT_LANGUAGE']);

        return Config::instance()->default_locale;
    }

    /**
     * Checks if the locale can be retrieved or stored in the cookie
     *
     * @param $strategy
     * @param $options
     * @return bool
     */
    static function isLocaleCookieEnabled($strategy, $locale_options)
    {
        if ($strategy == 'pre-domain' || $strategy == 'custom-domain')
            return false;
        else
            return ($locale_options && isset($locale_options['cookie'])) ? $locale_options['cookie'] : true;
    }

    /**
     * Returns requested locale from param, pre-path or domain options
     *
     * @param $options
     * @return null|string
     */
    static function getRequestedLocale()
    {
        $strategy = self::localeStrategy();
//        $default_locale = isset($locale_options['default']) ? $locale_options['default'] : Config::instance()->default_locale;

        # if locale has been passed by a param, it will be in the params hash
        if ($strategy == 'param') {
            $param = self::localeParam();
            return (isset($_GET[$param]) ? $_GET[$param] : null);
        }

        if ($strategy == 'pre-path') {
            if ($_SERVER["REQUEST_URI"] === '')
                return null;

            $path = $_SERVER["REQUEST_URI"];
            $prefix = self::localePathPrefix();

            if ($prefix !== null) {
                $path = preg_replace('/^' . str_replace('/', '\/', $prefix) . '/', '', $path);
            }

            $fragments = StringUtils::split($path, '/');
            if (count($fragments) == 0)
                return null;
            return Config::instance()->isValidLocale($fragments[0]) ? $fragments[0] : null;
        }

        if ($strategy == 'pre-domain') {
            $fragments = StringUtils::split($_SERVER["HTTP_HOST"], '.');
            return Config::instance()->isValidLocale($fragments[0]) ? $fragments[0] : null;
        }

        if ($strategy == 'custom-domain') {
            $mapping = array_flip(self::localeMapping());
            $host = $_SERVER["HTTP_HOST"];
            return isset($mapping[$host]) ? $mapping[$host] : null;
        }

        return null;
    }

    /**
     * @param array $options
     */
    static function finalize($options = array())
    {
        Session::instance()->completeRequest($options);
        global $tml_page_t0;
        $milliseconds = round(microtime(true) - $tml_page_t0, 3) * 1000;
        Logger::instance()->info("Page loaded in " . $milliseconds . " milliseconds");
    }

    static function localeOptions() {
        return array(
            "strategy" => self::localeStrategy(),
            "param" => self::localeParam(),
            "prefix" => self::localePathPrefix(),
            "mapping" => self::localeMapping(),
            "default_subdomain" => self::localeDefaultSubdomain(),
            "skip_default" => self::localeSkipDefault(),
        );
    }

    static function localeOption($name, $default = null)
    {
        if (!self::instance()->locale_options)
            return $default;
        return isset(self::instance()->locale_options[$name]) ? self::instance()->locale_options[$name] : $default;
    }

    static function localeSkipDefault() {
        return self::localeOption('skip_default', false);
    }

    static function useBrowserHeaderLocale()
    {
        return self::localeOption('browser', true);
    }

    static function localeStrategy()
    {
        return self::localeOption('strategy', 'param');
    }

    static function localePathPrefix()
    {
        return self::localeOption('prefix');
    }

    static function localeParam()
    {
        return self::localeOption('param', 'locale');
    }

    static function localeMapping()
    {
        return self::localeOption('mapping', array());
    }

    static function localeDefaultSubdomain()
    {
        return self::localeOption('default_subdomain');
    }

    /**
     * @return \Tml\Application
     */
    static function application()
    {
        return self::instance()->application;
    }

    /**
     * @return \Tml\Language
     */
    static function currentLocale()
    {
        return self::instance()->current_language->locale;
    }

    /**
     * @return string
     */
    static function currentSource()
    {
        return self::instance()->current_source;
    }

    /**
     * @return \Tml\Language
     */
    static function currentLanguage()
    {
        return self::instance()->current_language;
    }

    /**
     * @return string
     */
    static function currentLanguageDirection()
    {
        return self::currentLanguage()->direction();
    }

    /**
     * @return \Tml\Translator
     */
    static function currentTranslator()
    {
        return self::instance()->current_translator;
    }

    /**
     * Opens the source block
     *
     * @param string $name
     */
    static function beginSource($name)
    {
        self::instance()->beginBlockWithOptions(array("source" => $name));
    }

    /**
     * Closes the source block
     */
    static function finishSource()
    {
        self::instance()->finishBlockWithOptions();
    }

    /**
     * There are three ways to call this method:
     *
     * 1. tr($label, $description = "", $tokens = array(), options = array())
     * 2. tr($label, $tokens = array(), $options = array())
     * 3. tr($params = array("label" => label, "description" => "", "tokens" => array(), "options" => array()))
     *
     * @param string $label
     * @param string $description
     * @param array $tokens
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    static function tr($label, $description = "", $tokens = array(), $options = array())
    {
        $params = ArrayUtils::normalizeTmlParameters($label, $description, $tokens, $options);

        # if in translation mode and is already translated, don't translate
        if (strpos($params['label'], 'tml:label') !== false) {
            return $params['label'];
        }

        try {
            // Translate individual sentences
            if (isset($params["options"]['split'])) {
                $sentences = StringUtils::splitSentences($params["label"]);
                foreach ($sentences as $sentence) {
                    $params["label"] = str_replace($sentence, self::currentLanguage()->translate($sentence, $params["description"], $params["tokens"], $params["options"]), $params["label"]);
                }
                return $label;
            }

            // Remove html and translate the content
            if (isset($params["options"]["strip"])) {
                $stripped_label = str_replace(array("\r\n", "\n"), '', strip_tags(trim($params["label"])));
                $translation = self::currentLanguage()->translate($stripped_label, $params["description"], $params["tokens"], $params["options"]);
                $label = str_replace($stripped_label, $translation, $params["label"]);
                return $label;
            }

            return self::currentLanguage()->translate($params["label"], $params["description"], $params["tokens"], $params["options"]);
        } catch (TmlException $ex) {
            Logger::instance()->error("Failed to translate " . $params["label"] . ": " . $ex);
            return $label;
        } catch (\Exception $ex) {
            Logger::instance()->error("ERROR: Failed to translate " . $params["label"] . ": " . $ex);
            throw $ex;
        }
    }

    /**
     * Translates a label and prints it to the page
     *
     * @param string $label
     * @param string $description
     * @param array $tokens
     * @param array $options
     */
    static function tre($label, $description = "", $tokens = array(), $options = array())
    {
        echo tr($label, $description, $tokens, $options);
    }

    /**
     * Translates a label while suppressing its decorations
     * The method is useful for translating alt tags, list options, etc...
     *
     * @param string $label
     * @param string $description
     * @param array $tokens
     * @param array $options
     * @return mixed
     */
    static function trl($label, $description = "", $tokens = array(), $options = array())
    {
        $params = ArrayUtils::normalizeTmlParameters($label, $description, $tokens, $options);
        $params["options"]["skip_decorations"] = true;
        return tr($params);
    }

    /**
     * Same as trl, but with printing it to the page
     *
     * @param string $label
     * @param string $description
     * @param array $tokens
     * @param array $options
     */
    static function trle($label, $description = "", $tokens = array(), $options = array())
    {
        echo trl($label, $description, $tokens, $options);
    }

    /**
     * Translates a block of html, converts it to TML, translates it and then converts it back to HTML
     *
     * @param $label
     * @param string $description
     * @param array $tokens
     * @param array $options
     * @return array
     */
    static function trh($label, $description = "", $tokens = array(), $options = array())
    {
        $params = ArrayUtils::normalizeTmlParameters($label, $description, $tokens, $options);

        # if in translation mode and is already translated, don't translate
        if (strpos($params['label'], 'tml:label') !== false) {
            return $params['label'];
        }

        $html = trim($params["label"]);
        $ht = new DomTokenizer($html, array(), $params["options"]);
        return $ht->translate();
    }

    /**
     * Translates a block of html, converts it to TML, translates it and then converts it back to HTML
     *
     * @param $label
     * @param string $description
     * @param array $tokens
     * @param array $options
     */
    static function trhe($label, $description = "", $tokens = array(), $options = array())
    {
        $params = ArrayUtils::normalizeTmlParameters($label, $description, $tokens, $options);
        echo trh($params);
    }

    /**
     * @param $key
     * @return null
     */
    static function blockOption($key)
    {
        return self::instance()->getBlockOption($key);
    }

    /**
     * @return bool
     */
    static function isActive()
    {
        return (self::instance()->application != null && self::instance()->application->key != null);
    }

    /**
     * @return bool
     */
    static function isInactive()
    {
        return !self::isActive();
    }
}
