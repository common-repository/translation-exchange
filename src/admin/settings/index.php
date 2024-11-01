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


if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

global $trex_api_strategy;

function create_plugin_link($slug)
{
    $action = 'install-plugin';
    return wp_nonce_url(
        add_query_arg(
            array(
                'action' => $action,
                'plugin' => $slug
            ),
            admin_url('update.php')
        ),
        $action . '_' . $slug
    );
}

?>

<script>
    function closeLightBox() {
        jQuery('#plugin_info').remove();
    }

    function openLightBox(url) {
        jQuery('#plugin_info').remove();
        var modal = jQuery('<div id="plugin_info" class="modal"><div class="modal_shadow"></div><iframe id="plugin_info_frame" class="modal_frame" src="about:blank"></iframe><div class="modal_close" onclick="closeLightBox()">&times;</div></div>');
        jQuery('body').append(modal);
        modal.show();
        jQuery('#plugin_info_frame').attr('src', url);
    }

    function showInstructions(title, logo_url, youtube_key) {
        jQuery('#plugin_info').remove();
        var modal = jQuery('<div id="plugin_info" class="modal"><div class="modal_shadow"></div><div id="modal_content" class="modal_frame" style="background: white;"></div><div class="modal_close" onclick="closeLightBox()">&times;</div></div>');
        jQuery('body').append(modal);
        modal.show();
        var html = [];
        html.push('<div style="margin-top: 20px;margin-bottom: 20px; text-align: center;">');
        html.push('<div><img src="' + logo_url + '" style="width: 100px; margin-bottom: 20px;"></div>');
        html.push('<h1>' + title + ' <?php _e('Integration Demo') ?></h1>');
        html.push('<div class="screen" style="text-align:center; margin-top: 50px;">');
        html.push('<iframe class="youtube" width="560" height="315" src="https://www.youtube.com/embed/' + youtube_key + '" frameborder="0" allowfullscreen></iframe>');
        html.push('</div>');
        html.push('</div>');
        jQuery('#modal_content').html(html.join(''));
    }

    function showHowItWorks() {
        jQuery('#plugin_info').remove();
        var modal = jQuery('<div id="plugin_info" class="modal"><div class="modal_shadow"></div><div id="modal_content" class="modal_frame" style="background: white;"></div><div class="modal_close" onclick="closeLightBox()">&times;</div></div>');
        jQuery('body').append(modal);
        modal.show();
        var html = [];
        html.push('<div style="margin-top: 20px;margin-bottom: 20px; text-align: center; color: #666;">');
        html.push('<h1 style="color: #666;"><?php _e('How does this plugin work?') ?></h1>');
        html.push('<div><img src="<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/diagram.png" ?>" style="width: 450px; margin-bottom: 10px; margin-top: 30px;"></div>');
        html.push('<div style="text-align:left; width: 70%; margin:auto; margin-top: 20px;">');
        html.push('<p style="font-size: 15px;"><?php _e('The Translation Exchange plugin provides an integration layer with the most popular localization plugins, including WPML, Polylang and qTranslate. It connects you to our translation management platform so you can order professional translations for your content in hundreds of languages.') ?></p>');
        html.push('<p style="font-size: 15px;"><?php _e('When translations are completed, they can immediately be published to your site.') ?></p>');
        html.push('<p style="font-size: 15px;"><?php _e('No additional configuration in the plugin is required. All you need to do now is to open Translation Exchange dashboard, import posts, translate them and publish them right back to WordPress.') ?></p>');
        html.push('<p style="font-size: 15px; text-align: center"><?php _e('It’s that easy!') ?></p>');
        html.push('</div>');
        html.push('</div>');
        jQuery('#modal_content').html(html.join(''));
    }
</script>

<style>
    .screen {
        background-image: url('<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/screen.png" ?>');
    }
</style>
<link rel='stylesheet' href='<?php echo plugin_dir_url(__FILE__) . "../../../assets/css/styles.css" ?>' type='text/css'
      media='all'/>

<div class="wrap">
    <?php
    $options = isset($_GET['options']);
    if (isset($_GET['trex'])) {
        update_option('translation-exchange-selected', true);
    }
    ?>
    <?php if ($trex_api_strategy->getName() != 'default' && !$options) { ?>
        <?php include_once dirname(__FILE__) . "/instructions/" . $trex_api_strategy->getName() . ".php"; ?>

    <?php } elseif (get_option('translation-exchange-selected', false) && !$options) { ?>

        <?php include_once dirname(__FILE__) . "/default.php"; ?>

    <?php } else { ?>

        <?php include_once dirname(__FILE__) . "/plugins.php"; ?>

    <?php } ?>
</div>
