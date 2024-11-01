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

class PlainDecorator extends Base {

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
        return $translated_label;
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
        return $transformed;
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
       return $value;
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
        return $value;
    }
}
