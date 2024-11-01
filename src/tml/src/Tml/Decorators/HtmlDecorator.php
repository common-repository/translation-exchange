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

namespace Tml\Decorators;

use Tml\Session;

class HtmlDecorator extends Base {

    /**
     * Decorates labels
     *
     * @param string $translated_label
     * @param \Tml\Language $translation_language
     * @param \Tml\Language $target_language
     * @param \Tml\TranslationKey $translation_key
     * @param array $options
     * @return mixed|string|\Tml\Language
     */
    public function decorate($translated_label, $translation_language, $target_language, $translation_key, $options) {
        if (!$this->isEnabled($options)) return $translated_label;

        if ($translation_key->application !== null &&
            $translation_key->application->isFeatureEnabled("lock_original_content") &&
            $translation_key->locale == $target_language->locale) return $translated_label;

        $classes = array('tml_translatable');

        if ($translation_key->isLocked()) {
            if (Session::instance()->current_translator->isFeatureEnabled('show_locked_keys')) {
                array_push($classes, 'tml_locked');
            } else {
                return $translated_label;
            }
        } else if ($translation_language->locale == $translation_key->language->locale) {
            if (isset($options["pending"]))
                array_push($classes, 'tml_pending');
            else
                array_push($classes, 'tml_not_translated');
        } else if ($translation_language->locale == $target_language->locale) {
            array_push($classes, 'tml_translated');
        } else {
            array_push($classes, 'tml_fallback');
        }

        $element = $this->getDecorationElement("tml:label", $options);

        $html = "<".$element." class='" . implode(' ', $classes);
        $html = $html . "' data-translation_key='" . $translation_key->key;
        $html = $html . "' data-target_locale='" . $target_language->locale;
        $html = $html . "'>";
        $html = $html . $translated_label;
        $html = $html . "</".$element.">";

        return $html;
    }

    /**
     * Decorates language cases
     *
     * @param \Tml\LanguageCase $language_case
     * @param \Tml\LanguageCaseRule $rule
     * @param string $original
     * @param string $transformed
     * @param array $options
     * @return mixed
     */
    public function decorateLanguageCase($language_case, $rule, $original, $transformed, $options) {
        if (!$this->isEnabled($options)) return $transformed;

        $data = array(
            'keyword'       => $language_case->keyword,
            'language_name' => $language_case->language->english_name,
            'latin_name'    => $language_case->latin_name,
            'native_name'   => $language_case->native_name,
            'conditions'    => ($rule ? $rule->conditions : ''),
            'operations'    => ($rule ? $rule->operations : ''),
            'original'      => $original,
            'transformed'   => $transformed
        );

        $attributes = array(
            'class'         => 'tml_language_case',
            'data-locale'   => $language_case->language->locale,
            'data-rule'     => urlencode(str_replace("\n", '', base64_encode(json_encode($data))))
        );

        $query = array();
        foreach($attributes as $name => $value) {
            array_push($query, $name . "='" . str_replace("'", "\\'", $value) . "'");
        }

        $element = $this->getDecorationElement("tml:case", $options);

        $html = "<" . $element . " " . implode(" ", $query) . ">";
        $html = $html . $transformed;
        $html = $html . "</".$element.">";

        return $html;
    }

    /**
     * Decorates tokens
     *
     * @param \Tml\Tokens\DataToken $token
     * @param $value
     * @param $options
     * @return string
     */
    public function decorateToken($token, $value, $options) {
        if (!$this->isEnabled($options)) return $value;

        $element = $this->getDecorationElement("tml:token", $options);
        $classes = array('tml_token', 'tml_token_' . $token->getDecorationName());

        $html = "<" . $element . " class='" . implode(" ", $classes) . "' data-name='" . $token->short_name . "'";
        if (!empty($token->context_keys))
            $html = $html . " data-context='" . implode(" ", $token->context_keys) . "'";
        if (!empty($token->case_keys))
            $html = $html . " data-case='" . implode(" ", $token->case_keys) . "'";
        $html = $html . ">";
        $html = $html . $value;
        $html = $html . "</".$element.">";

        return $html;
    }

    /**
     * Decorates array elements
     *
     * @param \Tml\Tokens\DataToken $token
     * @param $value
     * @param $options
     * @return string
     */
    public function decorateElement($token, $value, $options) {
        if (!$this->isEnabled($options)) return $value;

        $element = $this->getDecorationElement("tml:element", $options);

        $html = "<" . $element . ">";
        $html = $html . $value;
        $html = $html . "</".$element.">";

        return $html;
    }

}
