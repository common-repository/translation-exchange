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
 * The client side option
 */
function trex_enqueue_script()
{
    global $trex_api_strategy;
    if (!$trex_api_strategy->isJavaScriptEnabled())
        return;

    $tml_script_host = get_option("tml_script_host");
    if (empty($tml_script_host)) $tml_script_host = "https://cdn.translationexchange.com/tools/tml/stable/tml.min.js";

    wp_register_script('tml_js', $tml_script_host, false, null, false);
    wp_register_script('tml_init', plugins_url('/../assets/javascripts/init.js', __FILE__), false, null, false);
    wp_enqueue_script('tml_js');
    wp_enqueue_script('tml_init');

    $tml_host = get_option('tml_host');
    if (empty($tml_host)) $tml_host = "https://api.translationexchange.com";

    $options = array(
        "host" => $tml_host,
        "key" => get_option('tml_key'),
        "advanced" => get_option('tml_script_options'),
        "locale" => array(
            "strategy" => get_option('tml_locale_selector'),
            "param" => "locale",
            "cookie" => "true",
            "prefix" => parse_url(get_site_url(), PHP_URL_PATH)
        )
    );

    if (get_option("tml_cache_type") == "local" && get_option("tml_cache_version") != '0') {
        $options['cache'] = array(
            "path" => plugins_url("translation-exchange/cache"),
            "version" => get_option('tml_cache_version')
        );
    }

    wp_localize_script('tml_init', 'TmlConfig', $options);
}

add_action('wp_enqueue_scripts', 'trex_enqueue_script');


/**
 * Add Translation Exchange settings menu
 */
function trex_settings()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    include('admin/settings/index.php');
}

/*
 * Admin Settings
 */
function trex_menu_pages()
{
    add_options_page( 'Translation Exchange Settings', 'Translation Exchange', 'manage_options', 'trex-settings', 'trex_settings' );
}

add_action('admin_menu', 'trex_menu_pages');
