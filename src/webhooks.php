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
 * Get webhook by key
 *
 * @param $key
 */
function trex_get_webhook($key)
{
    $webhooks = get_option('trex_api_webhooks');

    if (!$webhooks || !is_array($webhooks) || !isset($webhooks[$key]))
        return null;

    return $webhooks[$key];
}

/**
 * Trigger workflows
 *
 * @param $post_id
 * @return bool
 */
function trex_webhook_save_post($post_id)
{
    global $disable_webhooks;
    if ($disable_webhooks) return;

    global $trex_api_strategy;

    // don't care about saving translations
    if (!$trex_api_strategy->isOriginalPost($post_id))
        return;

    $body = array(
        "post_id" => $post_id,
        "post_type" => get_post_type($post_id)
    );

    $post_parent_id = wp_get_post_parent_id($post_id);
    if ($post_parent_id) {
        $body['parent_id'] = $post_parent_id;
        $body['parent_type'] = get_post_type($post_parent_id);
    }

    try {
        $webhook = trex_get_webhook('save_post');
        if (!$webhook) return;

        wp_remote_post($webhook["url"], array(
                'method' => 'POST',
                'timeout' => 10,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => false,
                'body' => $body
            )
        );

    } catch (Exception $e) {
//        echo 'Caught exception on triggering workflow: ', $e->getMessage(), "\n";
    }

    return true;
}
add_action('save_post', 'trex_webhook_save_post');
