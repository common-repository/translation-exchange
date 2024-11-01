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

namespace Tml\Utils;

class BrowserUtils {

    public static function acceptedLocales($header = null) {
        $weighted_locales = self::parseLanguageList($header);
        $weights = array_keys($weighted_locales);
        sort($weights);
        $weights = array_reverse($weights);
        $locales = array();
        foreach($weights as $weight) {
            foreach($weighted_locales[$weight] as $l) {
              array_push($locales, $l);
            }
        }
        return implode(',', $locales);
    }

    public static function parseLanguageList($languageList = null) {
        if (is_null($languageList)) {
            if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return array();
            }
            $languageList = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }
        $languages = array();
        $languageRanges = explode(',', trim($languageList));
        foreach ($languageRanges as $languageRange) {
            if (preg_match('/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/', trim($languageRange), $match)) {
                if (!isset($match[2])) {
                    $match[2] = '1.0';
                } else {
                    $match[2] = (string) floatval($match[2]);
                }
                if (!isset($languages[$match[2]])) {
                    $languages[$match[2]] = array();
                }
                $languages[$match[2]][] = $match[1];
            }
        }
        krsort($languages);
        return $languages;
    }

    public static function findMatches($accepted, $available) {
        $matches = array();
        $any = false;
        foreach ($accepted as $acceptedQuality => $acceptedValues) {
            $acceptedQuality = floatval($acceptedQuality);
            if ($acceptedQuality === 0.0) continue;
            foreach ($available as $availableQuality => $availableValues) {
                $availableQuality = floatval($availableQuality);
                if ($availableQuality === 0.0) continue;
                foreach ($acceptedValues as $acceptedValue) {
                    if ($acceptedValue === '*') {
                        $any = true;
                    }
                    foreach ($availableValues as $availableValue) {
                        $matchingGrade = self::matchLanguage($acceptedValue, $availableValue);
                        if ($matchingGrade > 0) {
                            $q = (string) ($acceptedQuality * $availableQuality * $matchingGrade);
                            if (!isset($matches[$q])) {
                                $matches[$q] = array();
                            }
                            if (!in_array($availableValue, $matches[$q])) {
                                $matches[$q][] = $availableValue;
                            }
                        }
                    }
                }
            }
        }
        if (count($matches) === 0 && $any) {
            $matches = $available;
        }
        krsort($matches);
        return $matches;
    }

    // compare two language tags and distinguish the degree of matching
    public static function matchLanguage($a, $b) {
        $a = explode('-', $a);
        $b = explode('-', $b);
        for ($i=0, $n=min(count($a), count($b)); $i<$n; $i++) {
            if ($a[$i] !== $b[$i]) break;
        }
        return $i === 0 ? 0 : (float) $i / count($a);
    }
}
