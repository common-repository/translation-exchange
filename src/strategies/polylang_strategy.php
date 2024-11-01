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

class PolylangStrategy extends DefaultStrategy
{

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'polylang';
    }

    /**
     * Return supported languages
     *
     * @return mixed
     */
    public function getSupportedLocales()
    {
        return pll_languages_list(array());
    }

    /**
     * Return default locale
     *
     * @return mixed
     */
    public function getDefaultLocale()
    {
        return pll_default_language();
    }

    /**
     * Checks if the post is in the original language
     *
     * @param $post_id
     * @return bool
     */
    public function isOriginalPost($post_id)
    {
        $locale = pll_get_post_language($post_id);
        return ($locale == $this->getDefaultLocale());
    }

    /**
     * Insert or update blog post or page
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

        $data = $this->preparePostParams($params);

        if ($locale == pll_default_language()) {
            return 'Translation cannot have the same locale as the original post';
        }

        $translated_post_id = pll_get_post($original_post_id, $locale);

        if ($translated_post_id) {
            $data['ID'] = $translated_post_id;
            wp_insert_post($data);
            $this->updateExtraPostContent($original_post_id, $translated_post_id, $params);

        } else {
            $translated_post_id = wp_insert_post($data);
            $this->updatePostMetadata($original_post_id, $translated_post_id);
            $this->updateExtraPostContent($original_post_id, $translated_post_id, $params);

            pll_set_post_language($translated_post_id, $locale);

            $language = pll_default_language();
            $mapping = array(
                $language => $original_post_id,
                $locale => $translated_post_id
            );

            $languages = pll_languages_list();
            foreach ($languages as $language) {
                $post_id = pll_get_post($original_post_id, $language);
                if ($post_id) $mapping[$language] = $post_id;
            }

            pll_save_post_translations($mapping);
        }

        return get_post($translated_post_id);
    }

}