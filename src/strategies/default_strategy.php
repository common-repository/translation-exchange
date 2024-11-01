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
class DefaultStrategy
{
    var $language_mapping;

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName()
    {
        return 'default';
    }

    /**
     * Return language mapping tables
     */
    public function getLanguageMapping()
    {
        if (!$this->language_mapping) {
            $this->language_mapping = array(
                "external" => json_decode(file_get_contents(dirname(__FILE__) . '/mapping/' . $this->getName() . '.json'), true),
                "internal" => array()
            );
            $external = $this->language_mapping['external'];
            foreach ($external as $locale => $data) {
                if (isset($this->language_mapping['internal'][$data['locale']]))
                    continue;
                $this->language_mapping['internal'][$data['locale']] = array('locale' => $locale, 'name' => $data['name']);
            }
        }

        return $this->language_mapping;
    }

    /**
     * Returns external locale based on internal locale
     *
     * @param $locale
     * @return null
     */
    public function getExternalLocale($locale)
    {
        $mapping = $this->getLanguageMapping();
        if (!isset($mapping['internal'][$locale]))
            return null;
        return $mapping['internal'][$locale]['locale'];
    }

    /**
     * Returns internal locale based on external locale
     *
     * @param $locale
     * @return null
     */
    public function getInternalLocale($locale)
    {
        $mapping = $this->getLanguageMapping();
        if (!isset($mapping['external'][$locale]))
            return null;
        return $mapping['external'][$locale]['locale'];
    }

    /**
     * Checks if default strategy is used
     *
     * @return bool
     */
    public function isDefault()
    {
        return ($this->getName() == 'default');
    }


    /**
     * Checks if JavaScript solution should be enabled
     */
    public function isJavaScriptEnabled()
    {
        return ($this->isDefault() && get_option('translation-exchange-selected', false));
    }

    /**
     * Debug vars
     *
     * @param $var
     */
    public function debug($var)
    {
//    error_log(var_export($var, true));
    }

    /**
     * Render API error
     *
     * @param $msg
     * @return array
     */
    public function renderApiError($msg)
    {
        return array('status' => 'error', 'message' => $msg);
    }

    /**
     * Prepare post parameters
     *
     * @param $params
     * @return array
     */
    public function preparePostParams($params)
    {
        return array(
            'post_author' => isset($params['author']) ? $params['author'] : '',
            'post_date' => isset($params['date']) ? $params['date'] : '',
            'post_date_gmt' => isset($params['date_gmt']) ? $params['date_gmt'] : '',
            'post_content' => isset($params['content']) ? $params['content'] : '',
            'post_content_filtered' => isset($params['content_filtered']) ? $params['content_filtered'] : '',
            'post_title' => isset($params['title']) ? $params['title'] : '',
            'post_excerpt' => isset($params['excerpt']) ? $params['excerpt'] : '',
            'post_status' => isset($params['status']) ? $params['status'] : 'draft',
            'post_type' => isset($params['type']) ? $params['type'] : 'page',
            'comment_status' => isset($params['comment_status']) ? $params['comment_status'] : 'closed',
            'ping_status' => isset($params['ping_status']) ? $params['ping_status'] : 'closed',
            'post_password' => isset($params['password']) ? $params['password'] : '',
            'post_name' => isset($params['name']) ? $params['name'] : '',
            'to_ping' => isset($params['to_ping']) ? $params['to_ping'] : '',
            'pinged' => isset($params['pinged']) ? $params['pinged'] : '',
            'post_modified' => isset($params['modified']) ? $params['modified'] : '',
            'post_modified_gmt' => isset($params['modified_gmt']) ? $params['modified_gmt'] : '',
            'post_parent' => isset($params['parent']) ? $params['parent'] : 0,
            'menu_order' => isset($params['menu_order']) ? $params['menu_order'] : 0,
            'post_mime_type' => isset($params['mime_type']) ? $params['mime_type'] : 0,
            'guid' => isset($params['guid']) ? $params['guid'] : '',
            'post_category' => isset($params['category']) ? $params['category'] : '',
            'tax_input' => isset($params['tax_input']) ? $params['tax_input'] : array(),
            'meta_input' => isset($params['meta_input']) ? $params['meta_input'] : array(),
        );
    }

    /**
     * Convert post to an array
     *
     * @param $post
     * @return array
     */
    public function postToJson($post)
    {
        $content = $post->post_content;
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);

        $title = $post->post_title;
        $title = apply_filters('the_title', $title);
        $title = str_replace(']]>', ']]&gt;', $title);

        return array(
            'id' => $post->ID,
            'date' => $post->post_date,
            'date_gmt' => $post->post_date_gmt,
            'modified' => $post->post_modified,
            'modified_gmt' => $post->post_modified_gmt,
            'slug' => $post->post_name,
            'link' => get_permalink($post),
            'status' => $post->post_status,
            'type' => $post->post_type,
            'author' => $post->post_author,
            'featured_media' => $post->post_featured_media,
            'parent' => $post->post_parent,
            'template' => $post->post_template,
            'format' => get_post_format($post),
            'content' => array(
                'plain' => $post->post_content,
                'rendered' => $content
            ),
            'title' => array(
                'plain' => $post->post_title,
                'rendered' => $title
            ),
            'categories' => wp_get_post_categories($post->ID),
            'tags' => wp_get_post_tags($post->ID)
        );
    }

    /**
     * Update extra post content
     *
     * @param $original_post_id
     * @param $translated_post_id
     * @param $data
     */
    function updateExtraPostContent($original_post_id, $translated_post_id, $data)
    {
        if (isset($data['extra']['themes']['Ichiban'])) {
            $ichiban = $data['extra']['themes']['Ichiban'];
            $original_meta = get_post_meta($original_post_id);

            if (isset($ichiban['splash'])) {
                $splash = unserialize($original_meta['splash'][0]);

                if (isset($ichiban['splash']['title'])) {
                    $splash['title'] = $ichiban['splash']['title'];
                }

                if (isset($ichiban['splash']['subtitle'])) {
                    $splash['subtitle'] = $ichiban['splash']['subtitle'];
                }

                if (isset($ichiban['splash']['content'])) {
                    $splash['content'] = $ichiban['splash']['content'];
                }

                update_post_meta($translated_post_id, 'splash', $splash);
            }
        }
    }

    /**
     * Update post metadata
     *
     * @param $original_post_id
     * @param $translated_post_id
     */
    function updatePostMetadata($original_post_id, $translated_post_id)
    {
        $original_meta = get_post_meta($original_post_id);

        foreach ($original_meta as $key => $value) {
            $this->debug($key);

            if (is_array($value))
                $value = $value[0];

            if (preg_match('/^a:\d+/', $value)) {
                $value = unserialize($value);
            }

            $this->debug($value);

            update_post_meta($translated_post_id, $key, $value);
        }
    }

    /**
     * Returns supported locales
     *
     * @return array
     */
    public function getSupportedLocales()
    {
        return array('en_US');
    }

    /**
     * Get all enabled languages
     *
     * @param $params
     * @return array
     */
    public function getLanguages($params)
    {
        $locales = $this->getSupportedLocales();
        $languages = array();

        foreach ($locales as $locale) {
            $external_locale = $this->getExternalLocale($locale);
            if (!$external_locale)
                continue;
            array_push($languages, $external_locale);
        }

        $languages = array_unique($languages);
        sort($languages);
        return array("languages" => $languages);
    }

    /**
     * Adds a languages to the WP
     *
     * @param $locale
     */
    public function addLanguage($locale)
    {
        // do nothing
    }

    /**
     *
     * @param $params
     * @return array
     */
    public function addLanguages($params)
    {
        if (!isset($params['locales']))
            return $this->renderApiError('Locales must be provided');

        $locales = $params['locales'];

        if (is_string($locales))
            $locales = explode(',', $locales);

        foreach ($locales as $locale) {
            $this->addLanguage($locale);
        }

        return array("languages" => $this->getSupportedLocales());
    }

    /**
     * Returns default locale
     *
     * @return mixed
     */
    public function getDefaultLocale()
    {
        return get_locale();
    }

    /**
     * Get default language
     *
     * @param $params
     * @return string
     */
    public function getDefaultLanguage($params)
    {
        $locale = $this->getExternalLocale($this->getDefaultLocale());
        return array("language" => $locale);
    }

    /**
     * Prepares pagination
     *
     * @param $page
     * @param $per_page
     * @param $total_count
     * @return array
     */
    public function pagination($page, $per_page, $total_count)
    {
        $total_pages = round($total_count / $per_page);
        if ($total_count % $per_page > 0)
            $total_pages = $total_pages + 1;

        return array(
            'page' => $page,
            'per_page' => $per_page,
            'total_count' => $total_count,
            'total_pages' => $total_pages
        );
    }

    /**
     * Appends extra content for the post
     *
     * @param $post_id
     * @param $data
     * @return mixed
     */
    public function appendExtraPostContent($post_id, $data)
    {
        $theme = wp_get_theme();
        $data['extra'] = array();

        if ('Ichiban' == $theme->name) {
            $data['extra'] = array();

            $meta = get_post_meta($post_id);
            if (isset($meta['splash']) && is_array($meta) && count($meta['splash']) > 0) {
                $splash = unserialize($meta['splash'][0]);
                $splash_content = array();
                if (isset($splash['title']))
                    $splash_content['title'] = $splash['title'];
                if (isset($splash['subtitle']))
                    $splash_content['subtitle'] = $splash['subtitle'];
                if (isset($splash['content']))
                    $splash_content['content'] = $splash['content'];
                $data['extra']['themes']['Ichiban']['splash'] = $splash_content;
            }

        }

        return $data;
    }

    /**
     * Get posts
     *
     * @param $params
     * @return array
     */
    public function getPosts($params)
    {
        $query = array(
            's' => isset($params['s']) ? $params['s'] : '',
            'category' => isset($params['category']) ? $params['category'] : '',
            'category_name' => isset($params['category_name']) ? $params['category_name'] : '',
            'orderby' => isset($params['orderby']) ? $params['orderby'] : 'date',
            'order' => isset($params['order']) ? $params['order'] : 'DESC',
            'include' => isset($params['include']) ? $params['include'] : '',
            'exclude' => isset($params['exclude']) ? $params['exclude'] : '',
            'meta_key' => isset($params['meta_key']) ? $params['meta_key'] : '',
            'meta_value' => isset($params['meta_value']) ? $params['meta_value'] : '',
            'post_type' => isset($params['post_type']) ? $params['post_type'] : 'post',
            'post_mime_type' => isset($params['post_mime_type']) ? $params['post_mime_type'] : '',
            'post_parent' => isset($params['post_parent']) ? $params['post_parent'] : '',
            'author' => isset($params['author']) ? $params['author'] : '',
            'author_name' => isset($params['author_name']) ? $params['author_name'] : '',
            'post_status' => isset($params['post_status']) ? $params['post_status'] : 'publish',
            'suppress_filters' => isset($params['suppress_filters']) ? $params['suppress_filters'] : false
        );

        return $this->filterAndPaginatePosts(get_posts($query), $params);
    }

    /**
     * Filters posts
     *
     * @param $posts
     * @return mixed
     */
    public function filterPosts($posts, $filter)
    {
        return $posts;
    }

    /**
     * Get a post
     *
     * @param $params
     * @return array|mixed
     */
    public function getPost($params)
    {
        if (!isset($params['id'])) {
            return $this->renderApiError('Post id must be provided');
        }

        $post = get_post($params['id']);
        $data = $this->postToJson($post);
        $data = $this->appendExtraPostContent($params['id'], $data);
        return $data;
    }

    /**
     * Returns post translations
     *
     * @param $params
     * @return array
     */
    public function getPostTranslations($params)
    {
        return array();
    }

    /**
     * Returns page translations
     *
     * @param $params
     * @return array
     */
    public function getPageTranslations($params)
    {
        return $this->getPostTranslations($params);
    }

    /**
     * Get pages
     *
     * @param $params
     * @return array
     */
    public function getPages($params)
    {
        $query = array(
            's' => isset($params['s']) ? $params['s'] : '',
            'sort_order' => isset($params['sort_order']) ? $params['sort_order'] : 'asc',
            'sort_column' => isset($params['sort_column']) ? $params['sort_column'] : 'post_title',
            'hierarchical' => isset($params['hierarchical']) ? $params['hierarchical'] : 1,
//        'exclude' => isset($params['exclude']) ? $params['exclude'] :'',
//        'include' => isset($params['include']) ? $params['include'] :'',
            'meta_key' => isset($params['meta_key']) ? $params['meta_key'] : '',
            'meta_value' => isset($params['meta_value']) ? $params['meta_value'] : '',
            'authors' => isset($params['authors']) ? $params['authors'] : '',
            'child_of' => isset($params['child_of']) ? $params['child_of'] : 0,
            'parent' => isset($params['parent']) ? $params['parent'] : -1,
            'exclude_tree' => isset($params['exclude_tree']) ? $params['exclude_tree'] : '',
            'number' => isset($params['number']) ? $params['number'] : '',
            'post_type' => isset($params['post_type']) ? $params['post_type'] : 'page',
            'post_status' => isset($params['post_status']) ? $params['post_status'] : 'publish'
        );

        return $this->filterAndPaginatePosts(get_pages($query), $params);
    }

    /**
     * Filters and paginates posts
     *
     * @param $posts
     * @param $params
     * @return array
     */
    public function filterAndPaginatePosts($posts, $params)
    {
        $per_page = isset($params['per_page']) ? $params['per_page'] : 30;
        $page = isset($params['page']) ? $params['page'] : 1;
        $offset = ($page - 1) * $per_page;

        $filter = isset($params['filter']) ? $params['filter'] : array();
        $posts = $this->filterPosts($posts, $filter);
        $total_count = count($posts);

        $posts = array_slice($posts, $offset, $per_page);
        $results = array();

        foreach ($posts as $post) {
            array_push($results, $this->postToJson($post));
        }

        $pagination = $this->pagination($page, $per_page, $total_count);
        return array('results' => $results, 'pagination' => $pagination);
    }

    /**
     * Checks if the post is in the original language
     *
     * @param $post
     * @return bool
     */
    public function isOriginalPost($post_id)
    {
        return true;
    }

    /**
     * Get a page
     *
     * @param $params
     * @return array|mixed
     */
    public function getPage($params)
    {
        if (!isset($params['id'])) {
            return $this->renderApiError('Page id must be provided');
        }

        $page = get_page($params['id']);
        $data = $this->postToJson($page);
        $data = $this->appendExtraPostContent($params['id'], $data);
        return $data;
    }


    /**
     * Post translations
     *
     * @param $params
     * @param $post_type
     */
    public function postTranslations($params, $post_type)
    {
        $translated_post = $this->insertOrUpdateTranslation($params, $post_type);

        if (is_string($translated_post))
            return $this->renderApiError($translated_post);

        if (isset($params['publish']) && $params['publish'])
            wp_publish_post($translated_post->ID);

        return $this->postToJson($translated_post);
    }

    /**
     * Insert or update a translation
     *
     * @param $params
     * @param $post_type
     * @return array
     */
    public function insertOrUpdateTranslation($params, $post_type)
    {
        return 'You are currently not using any supported localization plugin, like WPML, Polylang, etc... This option is disabled.';
    }

}