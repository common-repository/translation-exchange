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

use Tml\Api\Client;
use Tml\Utils\ArrayUtils;

/**
 * Class Application
 *
 * Contains all information related to the current application
 *
 * @package Tml
 */
class Application extends Base {
    /**
     * @var string
     */
    public $host;

    /**
     * @var
     */
    public $cdn_host;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $access_token;
    /**
     * @var string
     */
    public $name;

    /**
     * @vars string
     */
    public $default_locale;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $threshold;

    /**
     * @var boolean[]
     */
    public $features;

    /**
     * @var string
     */
    public $css;

    /**
     * @var Language[]
     */
    public $languages;

    /**
     * @var Source[]
     */
    public $sources;

    /**
     * @var Language[]
     */
    public $languages_by_locale;

    /**
     * @var Source[]
     */
    public $sources_by_key;

    /**
     * @var array
     */
    public $translation_keys;

    /**
     * @var array
     */
    public $translations;

    /**
     * @var TranslationKey[]
     */
    public $missing_keys_by_sources;

    /**
     * @var Api\Client
     */
    private $api_client;

    /**
     * @var array
     */
    public $extensions;

    /**
     * @return $this
     */
    public function fetch() {
        Logger::instance()->info("Initializing application...");

        $data = $this->apiClient()->get("projects/" . $this->key . "/definition", array(
                'locale' => Session::instance()->current_locale,
                'source' => Session::instance()->current_source,
                'ignored' => 'true'
            ),
            array('cache_key' => self::cacheKey())
        );

        if ($data === null) {
            $this->addLanguage(Config::instance()->defaultLanguage());
        } else {
            $this->updateAttributes($data);
        }

        return $this;
    }

    /**
     * @return Application
     */
    public static function dummyApplication() {
        Logger::instance()->info("Falling back onto dummy application...");

        $app = new Application();
        $app->name = "Disconnected Application";
        $default_language = Config::instance()->defaultLanguage();
        $default_language->application = $app;
        $app->languages_by_locale = array(
            Config::instance()->default_locale => $default_language
        );
        return $app;
    }

    /**
     * @param array $attributes
     */
    function __construct($attributes=array()) {
        parent::__construct($attributes);

        $this->updateAttributes($attributes);
    }

    /**
     * @param array $attributes
     */
    function updateAttributes($attributes=array()) {
        parent::__construct($attributes);

        if (isset($attributes['key']))
            $this->key = $attributes['key'];

        $this->languages = array();
        if (isset($attributes['languages'])) {
            foreach($attributes['languages'] as $l) {
                array_push($this->languages, new Language(array_merge($l, array("application" => $this))));
            }
        }

        $this->sources = array();
        if (isset($attributes['sources'])) {
            foreach($attributes['sources'] as $l) {
                array_push($this->sources, new Source(array_merge($l, array("application" => $this))));
            }
        }

        if (isset($attributes['features'])) {
            $this->features = $attributes['features'];
        }

        if (isset($attributes['css'])) {
            $this->css = $attributes['css'];
        }

        $this->languages_by_locale  = null;
        $this->sources_by_key       = null;
        $this->translation_keys     = array();
        $this->missing_keys_by_sources = null;

        if (isset($attributes['extensions'])) {
            $this->extensions = $attributes['extensions'];
            $this->loadExtensions($attributes['extensions']);
        }
    }

    /**
     * @param $extensions
     */
    function loadExtensions($extensions) {
        $source_locale = $this->default_locale;
        $use_cache = (Config::instance()->isCacheEnabled() && !Session::instance()->isInlineTranslationModeEnabled());

        if (isset($extensions['languages'])) {
            $this->languages_by_locale = array();
            foreach($extensions['languages'] as $locale => $data) {
                if ($source_locale != $locale)
                    $source_locale = $locale;

                if ($use_cache) Cache::store(Language::cacheKey($locale), $data);

                $language = new Language(array_merge(
                    $data,
                    array("application" => $this, "locale" => $locale)
                ));

                $this->languages_by_locale[$locale] = $language;
            }
        }

        if (isset($extensions['sources'])) {
            $this->sources_by_key = array();

            foreach($extensions['sources'] as $key => $data) {
                if ($use_cache) Cache::store(Source::cacheKey($key, $source_locale), $data);
                $source = new Source(array("application" => $this, "source" => $key));
                $source->addTranslations($source_locale, $data);
                $this->sources_by_key[$key] = $source;
            }
        }
    }

    /**
     * @return string
     */
    public static function cacheKey() {
        return "application";
    }

    /**
     * @param $locale
     * @return string
     */
    public static function translationsCacheKey($locale) {
        return $locale . DIRECTORY_SEPARATOR . "translations";
    }

    /**
     * Checks if a give locale is supported by the application
     *
     * @param string $locale
     * @return bool
     */
    public function isLocaleSupported($locale) {
        foreach($this->languages as $language) {
            if ($language->locale === $locale)
                return true;
        }

        return false;
    }

    /**
     * Finds the first available supported locale or returns default locale
     *
     * @param $locales
     * @return mixed
     */
    public function supportedLocale($locales) {
        if (is_string($locales))
            $locales = explode(",", $locales);

        foreach($locales as $l) {
            if ($this->isLocaleSupported($l)) {
                return $l;
            }
        }

        if ($this->default_locale != null)
            return $this->default_locale;

        return 'en';
    }

    /**
     * @param string|null $locale
     * @return Language
     */
    public function language($locale = null) {
        $locale = ($locale == null ? Config::instance()->default_locale : $locale);

        if ($this->languages_by_locale == null) {
            $this->languages_by_locale = array();
        }

        if (isset($this->languages_by_locale[$locale])) {
            return $this->languages_by_locale[$locale];
        }

        $language = $this->fetchLanguage($locale);

        if ($language === null) {
            $locale = str_replace("_","-",$locale);
            if (strpos($locale,'-') !== false) {
                $parts = explode('-', $locale);
                $locale = $parts[0];
                $language = $this->fetchLanguage($locale);
            }
        }

        if ($language === null) {
            $locale = Config::instance()->default_locale;
            $language = $this->fetchLanguage($locale);
        }

        $language->application = $this;
        $this->languages_by_locale[$locale] = $language;
        return $this->languages_by_locale[$locale];
    }

    /**
     * @param $locale
     * @return Language
     */
    public function fetchLanguage($locale) {
        return $this->apiClient()->get("languages/$locale/definition",
            array(),
            array("class" => '\Tml\Language',
                "attributes" => array("application" => $this, "locale" => $locale),
                "cache_key"  => Language::cacheKey($locale)
            )
        );
    }

    /**
     * @param Language $language
     * @return Language
     */
    public function addLanguage($language) {
        if (isset($this->languages_by_locale[$language->locale])) {
            return $this->languages_by_locale[$language->locale];
        }

        $language->application = $this;
        array_push($this->languages, $language);
        $this->languages_by_locale[$language->locale] = $language;

        return $language;
    }

    /**
     * @param $key
     * @param $locale
     * @return null|Source
     */
    public function source($key, $locale) {
        if ($this->sources_by_key == null)
            $this->sources_by_key = array();

        if (isset($this->sources_by_key[$key]))
            return $this->sources_by_key[$key];

        $source = new Source(array("application" => $this, "source" => $key));
        $source->fetchTranslations($locale);
        $this->sources_by_key[$key] = $source;
        return $this->sources_by_key[$key];
    }

    /**
     * @param string $key
     * @return null|TranslationKey
     */
    public function translationKey($key) {
        if (!isset($this->translation_keys[$key])) return null;
        return $this->translation_keys[$key];
    }

    /**
     * @param TranslationKey $translation_key
     * @return null|TranslationKey
     */
    public function cacheTranslationKey($translation_key) {
        $translation_key->setApplication($this);
        $this->translation_keys[$translation_key->key] = $translation_key;
        foreach($translation_key->translations as $locale => $translations) {
            $translation_key->setLanguageTranslations($this->language($locale), $translations);
        }
        return $translation_key;
    }

    /**
     * @param $source_key
     * @param $source_path
     */
    public function verifySourcePath($source_key, $source_path) {
        if (Config::instance()->isCacheEnabled() && !Session::instance()->isInlineTranslationModeEnabled())
            return;

        if (!$this->extensions || !isset($this->extensions["sources"]))
            return;

        if (isset($this->extensions["sources"][$source_key]))
            return;

        if ($this->missing_keys_by_sources === null)
            $this->missing_keys_by_sources = array();

        if (!isset($this->missing_keys_by_sources[$source_path]))
            $this->missing_keys_by_sources[$source_path] = array();
    }

    /**
     * @param TranslationKey $translation_key
     * @param string $source_path
     */
    public function registerMissingKey($translation_key, $source_path = 'index') {
        if (!Session::instance()->isKeyRegistrationEnabled())
            return;

        if ($this->missing_keys_by_sources === null) {
            $this->missing_keys_by_sources = array();
        }

        if (!isset($this->missing_keys_by_sources[$source_path])) {
            $this->missing_keys_by_sources[$source_path] = array();
        }

        if (!isset($this->missing_keys_by_sources[$source_path][$translation_key->key])) {
            $this->missing_keys_by_sources[$source_path][$translation_key->key] = $translation_key;
        }
    }

    /**
     * Submits missing keys to the service
     */
    public function submitMissingKeys() {
        if ($this->missing_keys_by_sources === null)
            return;

        $params = array();
        $source_keys = array();
        foreach($this->missing_keys_by_sources as $source => $keys) {
            array_push($source_keys, $source);

            $keys_data = array();
            foreach($keys as $key) {
                /** @var $key TranslationKey */
                $json = array(
                    "label"         => $key->label,
                    "description"   => $key->description,
                    "locale"        => $key->locale,
                    "level"         => $key->level
                );
                array_push($keys_data, $json);
            }
            array_push($params, array("source" => $source, "keys" => $keys_data));
        }

        $params = ArrayUtils::trim($params);
        $this->apiClient()->post('sources/register_keys', array("source_keys" => json_encode($params)));
        $this->missing_keys_by_sources = null;

        // All source caches must be reset for all languages, since the keys have changed
        foreach ($this->languages_by_locale as $locale => $language) {
            foreach ($source_keys as $source_key) {
                Cache::delete(Source::cacheKey($source_key, $locale));
            }
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function isFeatureEnabled($key) {
        if (!$this->features || !isset($this->features[$key]))
            return false;
        return $this->features[$key];
    }

    /**
     * @param $locale
     * @param $key
     * @return array|bool
     */
    public function fetchTranslations($locale, $key) {
        if (!$this->translations)
            $this->translations = array();

        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = array();

            try {
                $results = $this->apiClient()->get(
                    "projects/" . $this->key . "/translations",
                    array('locale' => $locale, 'all' => 'true', 'ignored' => 'true'),
                    array('cache_key' => self::translationsCacheKey($locale))
                );
            } catch (TmlException $e) {
                Logger::instance()->debug("Failed to fetch application translations");
                return array();
            }

            foreach($results as $key => $data) {
                if (isset($data['translations']))
                    $data = $data['translations'];

                $this->translations[$locale][$key] = array();

                foreach($data as $t) {
                    array_push($this->translations[$locale][$key], new Translation(array(
                        "locale" => isset($t["locale"]) ? $t["locale"] : $locale,
                        "label" => isset($t["label"]) ? $t["label"] : '',
                        "context" => isset($t["context"]) ? $t["context"] : null
                    )));
                }
            }
        }

        if (!array_key_exists($key, $this->translations[$locale])) return false;
        return $this->translations[$locale][$key];
    }

    /**
     * @param $locale
     * @param $key
     * @param $new_translations
     */
    public function cacheTranslations($locale, $key, $new_translations) {
        if (!$this->translations)
            $this->translations = array();
        if (!isset($this->translations[$locale]))
            $this->translations[$locale] = array();
        $this->translations[$locale][$key] = array();

        foreach($new_translations as $translation) {
            array_push($this->translations[$locale], new Translation(array(
                "locale" => (isset($translation["locale"]) ? $translation["locale"] : $locale),
                "label" => $translation["label"],
                "context" => (isset($translation["context"]) ? $translation["context"] : $locale),
            )));
        }
    }

    /**
     * @param $locale
     * @param $key
     * @return bool
     */
    public function cachedTranslations($locale, $key) {
        if (!isset($this->translations[$locale])) return false;
        if (!array_key_exists($key, $this->translations[$locale])) return false;
        return $this->translations[$locale][$key];
    }

    /**
     * @param array $keys
     * @return array
     */
    public function toArray($keys=array()) {
        $hash = parent::toArray(array("key", "host", "name", "default_locale", "threshold", "features", "css", "languages", "description"));
        $hash["languages"] = array();
        foreach($this->languages as $l) {
            array_push($hash["languages"], $l->toArray(array("locale", "name", "english_name", "native_name", "right_to_left", "flag_url")));
        }
        return $hash;
    }

    /**
     * @return Api\Client
     */
    public function apiClient() {
        if ($this->api_client == null) {
            $this->api_client = new Client($this);
        }
        return $this->api_client;
    }
}
