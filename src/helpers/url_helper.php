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

class UrlHelper
{
    public $method, $scheme, $host, $path, $query, $params, $locale;

    function __construct() {
        $this->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
        $this->scheme = is_ssl() ? 'https' : 'http';
        $this->host = $_SERVER['HTTP_HOST'];
        $this->query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        $this->params = array();
        parse_str($this->query, $this->params);

        $site_default_path = parse_url(get_site_url(), PHP_URL_PATH);

        $this->path = explode('?', $_SERVER['REQUEST_URI']);
        if (is_array($this->path))
            $this->path = $this->path[0];
        else
            $this->path = '';

        $expr = '/^' . str_replace('/', '\/', $site_default_path) . '/';
        // get rid of the default pre-path element
        $this->path = preg_replace($expr, '', $this->path);

        if ($this->isPrePath()) {
            if ($this->path !== '') {
                $elements = StringUtils::split($this->path, '/');

                if (count($elements) > 0 && $this->isValidLocale($elements[0]))
                    $this->locale = array_shift($elements);

                $this->path = '/' . StringUtils::join($elements, '/');
            }
        } elseif ($this->isPreDomain()) {
            $elements = StringUtils::split($this->host, '.');

            if ($this->isValidLocale($elements[0]))
                $this->locale = array_shift($elements);

            $this->host = StringUtils::join($elements, '.');
        } elseif ($this->isParamBased()) {
            if (isset($this->params['locale']))
                $this->locale = $this->params['locale'];
        } elseif ($this->isCustomUrl()) {

        }
        parse_str($this->query, $this->params);
        trex_log($this->to_array());
    }

    public function isValidLocale($locale) {
        return preg_match('/^[a-z]{2}(-[A-Z]{2,3})?$/', $locale);
    }

    public function isPreDomain() {
        return ('pre-domain' == get_option('tml_locale_selector'));
    }

    public function isPrePath() {
        return ('pre-path' == get_option('tml_locale_selector'));
    }

    public function isCustomUrl() {
        return ('custom' == get_option('tml_locale_selector'));
    }

    public function isParamBased() {
        return ('param' == get_option('tml_locale_selector'));
    }

    public function getCustomUrlForLocale($locale) {
        return get_option('tml_locale_url_' . $locale);
    }

    public function toHomeUrl($original_url, $path, $orig_scheme, $blog_id) {
        if (0 !== strpos($path, '/'))
            $path = '/' . $path;

        $url_host = '';

        // if original URL is a full url, so should be our response, otherwise we will just give back the path
        if (preg_match('/^http/', $original_url))
            $url_host = get_site_url();

        $url = $url_host . $path;

        if ($this->locale && $this->locale !== '') {
            if ($this->isPrePath())
                $url = $url_host . '/' . $this->locale . $path;
            else if ($this->isPreDomain())
                $url =  $this->scheme . '://' .  $this->locale . '.' . $this->host . $path;
            else if ($this->isParamBased()) {
//                $param = (strpos($path, '?') !== false) ? '&' : '?';
//                $url = $url . $param . 'locale=' . $this->locale;
            }
        }
        return $url;
    }

    public function toSource() {
        if ($this->path == '' || $this->path == '/')
            return '/index';

        $source = str_replace('.php', '', $this->path);
        return $source;
    }

    public function to_array() {
        return array(
            'method' => $this->method,
            'scheme' => $this->scheme,
            'host' => $this->host,
            'path' => $this->path,
            'query' => $this->query,
            'params' => $this->params,
            'locale' => $this->locale
        );
    }
}