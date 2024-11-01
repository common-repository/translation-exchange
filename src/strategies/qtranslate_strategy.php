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

class QTranslateStrategy extends DefaultStrategy
{
    /**
     * Return strategy name
     *
     * @return string
     */
    public function getName()
    {
        return 'qtranslate';
    }

    /**
     * Get supported languages
     *
     * @param $params
     * @return array
     */
    public function getSupportedLocales()
    {
        return get_option("qtranslate_enabled_languages", array('en'));
    }

    /**
     * Adds language to WordPress
     *
     * @param $locale
     */
    public function addLanguage($locale)
    {
        $locales = get_option("qtranslate_enabled_languages", array('en'));
        if (in_array($locale, $locales))
            return;

        array_push($locales, $locale);
        update_option("qtranslate_enabled_languages", $locales);
    }

    /**
     * Get default locale
     *
     * @return mixed
     */
    public function getDefaultLocale()
    {
        return get_option("qtranslate_default_language", 'en');
    }

    /**
     * @param $content
     * @return array
     */
    public function extractTranslations($content)
    {
        $pattern = '/(\[:[a-zA-Z\-_]*\])/';

        if (!preg_match($pattern, $content)) {
            return array($this->getDefaultLocale() => $content);
        }

        $parts = preg_split($pattern, $content, null, PREG_SPLIT_DELIM_CAPTURE);

        $translations = array();
        $key = null;
        foreach ($parts as $part) {
            if ($part == '[:]')
                continue;

            if (preg_match($pattern, $part)) {
                $key = $part;
                continue;
            }

            if ($key) {
                $key = preg_replace('/[\[\]:]/', '', $key);
                $translations[$key] = $part;
                $key = null;
            }
        }

        ksort($translations);
        return $translations;
    }

    /**
     * Serialize translations
     *
     * @param $content_by_locale
     * @return string
     */
    public function implodeTranslations($content_by_locale)
    {
        $locales = $this->getSupportedLocales();
        $default_locale = $this->getDefaultLocale();

        $data = array();
        array_push($data, "[:" . $default_locale . "]");
        array_push($data, $content_by_locale[$default_locale]);

        foreach ($locales as $locale) {
            if ($locale == $default_locale)
                continue;

            array_push($data, "[:" . $locale . "]");

            if (isset($content_by_locale[$locale])) {
                array_push($data, $content_by_locale[$locale]);
            } else {
                array_push($data, $content_by_locale[$default_locale]);
            }
        }

        array_push($data, '[:]');
        return implode('', $data);
    }

    /**
     * Returns post translations
     *
     * @param $params
     * @return array
     */
    public function getPostTranslations($params)
    {
        $per_page = isset($params['per_page']) ? $params['per_page'] : 30;
        $page = isset($params['page']) ? $params['page'] : 1;
        $offset = ($page - 1) * $per_page;

        if (!isset($params['id'])) {
            return $this->renderApiError('Original post id must be provided');
        }

        $original_post_id = $params['id'];

        $post = get_post($original_post_id);
        $content_by_locale = $this->extractTranslations($post->post_content);
        $title_by_locale = $this->extractTranslations($post->post_title);

        $total_count = count($content_by_locale);

        $default_locale = $this->getDefaultLocale();

        $translations = array();
        foreach ($content_by_locale as $locale => $label) {
            if (isset($title_by_locale[$locale]))
                $title = $title_by_locale[$locale];
            else
                $title = $title_by_locale[$default_locale];

            array_push($translations, array(
                "locale" => $locale,
                "title" => $title,
                "content" => $label
            ));
        }

        $translations = array_slice($translations, $offset, $per_page);
        $pagination = $this->pagination($page, $per_page, $total_count);

        return array("results" => $translations, "pagination" => $pagination);
    }

    /**
     * Insert or update post translation
     *
     * @param $params
     * @param $post_type
     * @return array
     */
    public function insertOrUpdateTranslation($params, $post_type)
    {
        if (!isset($params['id'])) {
            return 'Original post id must be provided';
        }

        $original_post_id = $params['id'];

        if (!isset($params['locale'])) {
            return 'Locale must be provided';
        }

        $locale = $params['locale'];

        if ($locale == $this->getDefaultLocale()) {
            return 'Translation cannot have the same locale as the original post';
        }

        $post = get_post($original_post_id);

        $content_by_locale = $this->extractTranslations($post->post_content);
        $title_by_locale = $this->extractTranslations($post->post_title);

        $content_by_locale[$locale] = $params['content'];
        $title_by_locale[$locale] = $params['title'];

        $data = array(
            "ID" => $original_post_id,
            "post_title" => $this->implodeTranslations($title_by_locale),
            "post_content" => $this->implodeTranslations($content_by_locale)
        );

        wp_insert_post($data);

        return get_post($original_post_id);
    }

}