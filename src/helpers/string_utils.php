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
 * GNU General Public License, version 2
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 */


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