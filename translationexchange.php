<?php
/*
  Plugin Name: Translation Exchange
  Plugin URI: http://wordpress.org/plugins/translationexchange/
  Description: Translate your WordPress site into any language in minutes.
  Author: Translation Exchange, Inc
  Version: 1.0.14
  Author URI: https://translationexchange.com/
  License: GPLv2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

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

if (!defined('ABSPATH')) exit;

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

add_option('tml_cache_type', 'none');
add_option('tml_cache_version', '0');
add_option('tml_locale_selector', 'param');

if (get_option('tml_locale_selector') == '')
    update_option('tml_locale_selector', 'param');

add_option('tml_cache_path', plugin_dir_path(__FILE__) . "cache");
if (get_option('tml_cache_path') == '')
    update_option('tml_cache_path', plugin_dir_path(__FILE__) . "cache");

require_once(dirname(__FILE__) . '/src/strategies/default_strategy.php');
require_once(dirname(__FILE__) . '/src/strategies/polylang_strategy.php');
require_once(dirname(__FILE__) . '/src/strategies/wpml_strategy.php');
require_once(dirname(__FILE__) . '/src/strategies/qtranslate_strategy.php');

require_once(dirname(__FILE__) . '/src/managers/plugin_manager.php');
require_once(dirname(__FILE__) . '/src/managers/theme_manager.php');

require_once(dirname(__FILE__) . '/src/helpers/string_utils.php');
require_once(dirname(__FILE__) . '/src/helpers/url_helper.php');
require_once(dirname(__FILE__) . '/src/helpers/debug.php');

global $trex_api_strategy;
global $file_manager;
global $disable_webhooks;

/**
 * Init Plugin
 */
function trex_init_plugin()
{
    global $trex_api_strategy;
    global $file_manager;

    if (is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
        $trex_api_strategy = new WpmlStrategy();
    } else if (is_plugin_active('polylang/polylang.php')) {
        $trex_api_strategy = new PolylangStrategy();
    } else if (is_plugin_active('qtranslate-x/qtranslate.php')) {
        $trex_api_strategy = new QTranslateStrategy();
    } else {
        global $url_helper;
        $url_helper = new UrlHelper();
        $trex_api_strategy = new DefaultStrategy();
    }
}
add_action('plugins_loaded', 'trex_init_plugin', 2);

include_once(plugin_dir_path(__FILE__) . '/src/basic_auth.php');

include_once(plugin_dir_path(__FILE__) . "/src/filters.php");
include_once(plugin_dir_path(__FILE__) . "/src/actions.php");
include_once(plugin_dir_path(__FILE__) . "/src/widgets.php");
include_once(plugin_dir_path(__FILE__) . '/src/webhooks.php');
include_once(plugin_dir_path(__FILE__) . '/src/routes.php');


