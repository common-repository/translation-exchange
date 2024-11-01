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

class Language extends Base {

    /**
     * @var Application
     */
    public $application;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $english_name;

    /**
     * @var string
     */
    public $native_name;

    /**
     * @var boolean
     */
    public $right_to_left;

    /**
     * @var LanguageContext[]
     */
    public $contexts;

    /**
     * @var LanguageCase[]
     */
    public $cases;

    /**
     * @var string
     */
    public $flag_url;

    /**
     * @var string
     */
    public $direction;

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
        $this->direction = $this->right_to_left ? "rtl" : "ltr";

        $this->contexts = array();
        if (isset($attributes['contexts'])) {
            foreach($attributes['contexts'] as $key => $context) {
                $this->contexts[$key] = new LanguageContext(array_merge($context, array("language" => $this, "keyword" => $key)));
            }
        }

        $this->cases = array();
        if (isset($attributes['cases'])) {
            foreach($attributes['cases'] as $key => $case) {
                $this->cases[$key] = new LanguageCase(array_merge($case, array("language" => $this, "keyword" => $key)));
            }
        }
    }

    /**
     * @param string $locale
     * @return string
     */
    public static function cacheKey($locale) {
        return $locale . DIRECTORY_SEPARATOR . "language";
    }

    /**
     * @param string $keyword
     * @return null|LanguageContext
     */
    public function contextByKeyword($keyword) {
        if (isset($this->contexts[$keyword]))
            return $this->contexts[$keyword];
        return null;
    }

    /**
     * @param string $token_name
     * @return null|LanguageContext
     */
    public function contextByTokenName($token_name) {
        foreach(array_values($this->contexts) as $ctx) {
            /** @var LanguageContext $ctx */
            if ($ctx->isAppliedToToken($token_name))
                return $ctx;
        }

        return null;
    }

    /**
     * @param string $key
     * @return null|LanguageCase
     */
    public function languageCase($key) {
        if (!array_key_exists($key, $this->cases))
            return null;

        return $this->cases[$key];
    }

    /**
     * @return string
     */
    public function flagUrl() {
        return $this->flag_url;
    }

    /*
     * By default, application fetches only the basic information about language,
     * so it can be displayed in the language selector. When languages are used for translation,
     * they must fetch full definition, including context and case rules.
     *
     * @return bool
     */
    public function hasDefinition() {
        return (count($this->contexts)>0);
    }

    /**
     * @return bool
     */
    public function isDefault() {
        if ($this->application == null) return true;
        return ($this->application->default_locale == $this->locale);
    }

    /**
     * @return string
     */
    public function direction() {
        return $this->right_to_left ? "rtl" : "ltr";
    }

    /**
     * @param $default
     * @return string
     */
    public function alignment($default) {
        if ($this->right_to_left) return $default;
        return ($default == "left") ? "right" : "left";
    }


    /**
     * @param array $options
     * @return null|Source
     */
    public function currentSource($options = array()) {
        $source_key = isset($options['source']) ? $options["source"] : Session::blockOption('source');
        if ($source_key == null) $source_key = Session::currentSource();
        return $source_key;
    }


    /**
     * @param $label
     * @param string $description
     * @param array $options
     * @return TranslationKey
     */
    private function createTranslationKey($label, $description = "", $options = array()) {
        $locale = isset($options["locale"]) ? $options["locale"] : Session::blockOption("locale");
        if ($locale == null) $locale = Config::instance()->default_locale;

        $level = isset($options["level"]) ? $options["level"] : Session::blockOption("level");
        if ($level == null) $level = Config::instance()->default_level;

        return new TranslationKey(array(
            "application"   => $this->application,
            "label"         => $label,
            "description"   => $description,
            "locale"        => $locale,
            "level"         => $level,
            "translations"  => array()
        ));
    }

    /**
     * @param string $label
     * @param string $description
     * @param array $token_values
     * @param array $options
     * @return string
     */
    public function translate($label, $description = "", $token_values = array(), $options = array()) {
        try {
//            if (strpos($label, '<tml:tr') !== false) return $label;

            $translation_key = $this->createTranslationKey($label, $description, $options);
            $token_values = array_merge($token_values, array("viewing_user" => Session::instance()->current_user));

            if (Session::isInactive()) {
                return $translation_key->substituteTokens($label, $token_values, $this, $options);
            }

            // most cache adapters use caching by source
            if (Cache::isCachedBySource() && !Session::blockOption("dry")) {
                $source_key = $this->currentSource($options);
                $source_path = $this->getSourcePath($source_key);

//                var_dump($label);
//                var_dump(Session::instance()->current_source);
//                var_dump($source_key);
//                var_dump($source_path);

                if (Session::blockOption("dynamic")) {
                    $source_path = $source_key;
                } else {
                    $this->application->verifySourcePath($source_key, $source_path);
                }

                $source = $this->application->source($source_key, $this->locale);

                if ($source->isIgnoredKey($translation_key->key)) {
                    $options['skip_decorations'] = true;
                    return $translation_key->substituteTokens($label, $token_values, $this, $options);
                }

                $matched_translations = $source->getCachedTranslations($this->locale, $translation_key->key);

                if ($matched_translations !== false) {
                    $translation_key->setTranslations($this->locale, $matched_translations);
                    return $translation_key->translate($this, $token_values, $options);
                }

                $this->application->registerMissingKey($translation_key, $source_path);
                return $translation_key->translate($this, $token_values, array_merge($options, array("pending" => true)));
            }

            $matched_key = $this->application->translationKey($translation_key->key);
            if ($matched_key != null) return $matched_key->translate($this, $token_values, $options);

            $temp_key = $translation_key->fetchTranslations($this, $options);
            return $temp_key->translate($this, $token_values, $options);
        } catch(\Exception $e) {
            Logger::instance()->error("Failed to translate: " . $label);
            return $label;
        }
	}

    /**
     * Returns full path of the source
     *
     * @param $current_source
     * @return string
     */
    function getSourcePath($current_source) {
        $source_path = array();
        $blocks = Session::instance()->block_options;
        if (!$blocks) $blocks = array();

        foreach($blocks as $opts) {
            if (isset($opts["source"]))
                array_unshift($source_path, $opts["source"]);
        }

        if (!in_array($current_source, $source_path)) {
            array_unshift($source_path, $current_source);
        }

        if (!in_array(Session::instance()->current_source, $source_path)) {
            array_unshift($source_path, Session::instance()->current_source);
        }

        return implode("@:@", $source_path);
    }

    /**
     * Returns string representation of the class
     * 
     * @return string
     */
    function __toString() {
        return $this->locale;
    }

}
