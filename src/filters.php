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

/**
 * Creates link from plugin to Settings
 *
 * @param $links
 * @param $file
 * @return mixed
 */
function trex_plugin_action_links_filter($links, $file) {
    if (preg_match('/tml|translationexchage|translation-exchange|trex/', $file)) {
        $settings_link = '<a href="' . admin_url('admin.php?page=trex-settings') . '">Settings</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'trex_plugin_action_links_filter', 10, 2);

/**
 * Ensures that the URL includes locale
 *
 * @param $url
 * @param $path
 * @param $orig_scheme
 * @param $blog_id
 * @return mixed
 */
function trex_home_url_filter($url, $path, $orig_scheme, $blog_id)
{
    global $trex_api_strategy;
    if ($trex_api_strategy && !$trex_api_strategy->isJavaScriptEnabled())
        return $url;

    global $url_helper;

    if (!$url_helper)
        return $url;

    $destination = $url_helper->toHomeUrl($url, $path, $orig_scheme, $blog_id);
//    error_log($destination);
    return $destination;
}
add_filter('home_url', 'trex_home_url_filter', 0, 4);

/**
 * Ensures that comments properly redirect
 *
 * @param $location
 * @return mixed
 */
function trex_comment_post_redirect_filter( $location ) {

    global $trex_api_strategy;
    if (!$trex_api_strategy->isJavaScriptEnabled())
        return $location;

    global $url_helper;

    if (!$url_helper)
        return $location;

    $referrer = null;
    if ( isset( $_SERVER["HTTP_REFERER"] ) ){
        $referrer = $_SERVER["HTTP_REFERER"];
    }

    if ($referrer === null)
        return $location;

    $parts = explode('#', $location);

    if (count($parts) > 1)
        $location = $referrer . '#' . $parts[1];
    else
        $location = $referrer;

    return $location;
}
add_filter( 'comment_post_redirect', 'trex_comment_post_redirect_filter' );
