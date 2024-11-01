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

class StringUtils {

    /**
     * @param $match
     * @param $str
     * @return bool|int
     */
    public static function startsWith($match, $str) {
        if (is_array($match)) {
            foreach($match as $option) {
                if (self::startsWith($option, $str)) return true;
            }
            return false;
        }
        return preg_match('/^'.$match.'/', $str) === 1;
    }

    /**
     * @param $match
     * @param $str
     * @return bool|int
     */
    public static function endsWith($match, $str) {
        if (is_array($match)) {
            foreach($match as $option) {
                if (self::endsWith($option, $str)) return true;
            }
            return false;
        }
        return preg_match('/'.$match.'$/', $str) === 1;
    }

    /**
     * Splits a value by delimiter
     *
     * @param $value
     * @param string $delimiter
     * @return array
     */
    public static function split($value, $delimiter = '/') {
        return array_values(array_filter(explode($delimiter, $value)));
    }

    /**
     * Joins elements together
     *
     * @param $array
     * @param string $joiner
     * @return string
     */
    public static function join($array, $joiner = '/') {
        return implode($joiner, $array);
    }

    /**
     * @param $text
     * @param array $opts
     * @return array
     */
    public static function splitSentences($text, /** @noinspection PhpUnusedParameterInspection */ $opts = array()) {
        $sentence_regex = '/[^.!?\s][^.!?]*(?:[.!?](?![\'"]?\s|$)[^.!?]*)*[.!?]?[\'"]?(?=\s|$)/';

        $matches = array();
        preg_match_all($sentence_regex, $text, $matches);
        $matches = array_unique($matches[0]);

        return $matches;
    }

    /**
     * Find the first match in the hash of mapped sources
     *
     * @param $source_mapping
     * @param $source
     * @return mixed
     */
    public static function matchSource($source_mapping, $source) {
        foreach ($source_mapping as $expr => $value) {
            if (preg_match($expr, $source) == 1)
                return $value;
        }
        return $source;
    }

    /**
     * @param $source
     * @return array|mixed
     */
    public static function normalizeSource($source) {
        $source = explode("#", $source);
        $source = $source[0];
        $source = explode("?", $source);
        $source = $source[0];
        $source = str_replace('.php', '', $source);
        $source = preg_replace('/\/$/', '', $source);
        return $source;
    }

    /**
     * @param $json
     * @return string
     */
    public static function prettyPrint($json) {
        $result = '';
        $level = 0;
        $prev_char = '';
        $in_quotes = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if( $char === '"' && $prev_char != '\\' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                    case '{': case '[':
                    $level++;

                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
                }
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
            $prev_char = $char;
        }

        return $result;
    }
}