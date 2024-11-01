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


?>

<?php
global $trex_api_strategy;
?>

<h2>
    <img src="<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/logo.png" ?>"
         style="width: 40px; vertical-align:middle; margin: 0px 5px;">
    <?php echo __('Translation Exchange Connectors'); ?>

    <div style="float:right; font-size: 14px; padding-top: 6px;">
        <a href="#" onclick="showHowItWorks(); return false;"><?php _e('How does it work?') ?></a>
        |
        <a href="https://translationexchange.com/" target="_new"><?php _e('Learn More') ?></a>
    </div>
</h2>

<hr/>

<div style="padding:20px;text-align: left; background: #eaeaea; margin-bottom: 15px;">
    <?php _e('Translation Exchange integrates well with the most popular WordPress translation plugins.') ?>
    <?php _e('As soon as you install a plugin, like Polylang, we will detect and start using it as the primary localization strategy.') ?>
    <?php _e('We also offer our own JavaScript based solution. But it`s completely up to you which plugin you prefer to use.') ?>
</div>

<div style="text-align: center">
    <div class="plugin">
        <div>
            <img
                src="<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/plugins/translationexchange.png" ?>"
                class="logo">
        </div>

        <div class="title">
            <a onclick="openLightBox('/wp-admin/plugin-install.php?tab=plugin-information&plugin=translation-exchange&TB_iframe=true'); return false;"
               href="#">
                Translation Exchange
            </a>
        </div>

        <div class="description">
            <?php _e('Translation Exchange provides a client-side JavaScript translation solution.') ?>
        </div>

        <div class="by">
            By <a href="https://translationexchange.com">Translation Exchange</a>
        </div>

        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="text-align:left">
                        <a onclick="showInstructions('Translation Exchange', '<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/plugins/translationexchange.png" ?>', '7u2bnE54TUk'); return false;" href="#">Watch Video</a>
                    </td>
                    <td style="text-align:right">
                        <?php if ($trex_api_strategy->getName() !== 'default') { ?>
                            <div class="help" style="display: inline-block; margin-left: 10px;">
                                ?
                                <div class="tooltip">
                                    This option is not available while you have at least one other option active.
                                    Deactivate <?php echo $trex_api_strategy->getName() ?> and this option will be enabled.
                                </div>
                            </div>
                        <?php } else { ?>
                            <a href="<?php echo admin_url('options-general.php?page=trex-settings&trex=true') ?>">Use</a>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="plugin">
        <div>
            <img src="<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/plugins/wpml.png" ?>"
                 class="logo">
        </div>

        <div class="title">
            <a onclick="openLightBox('https://wpml.org'); return false;" href="#">WPML Multilingual CMS</a>
        </div>

        <div class="description">
            <?php _e('WPML combines multilingual content authoring with powerful translation management.') ?>
        </div>
        <div class="by">
            By <a href="https://www.onthegosystems.com/">OnTheGoSystems</a>
        </div>

        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="text-align:left">
                        <a onclick="showInstructions('WPML', '<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/plugins/wpml.png" ?>', 'qgDb1nfPklE'); return false;" href="#">Watch Video</a>
                    </td>
                    <td style="text-align:right">
                        <?php if ($trex_api_strategy->getName() === 'wpml') { ?>
                            <strong>Active</strong>
                        <?php } elseif ($trex_api_strategy->getName() !== 'default') { ?>
                            <div class="help" style="display: inline-block; margin-left: 10px;">
                                ?
                                <div class="tooltip">
                                    This option is not available while you have at least one other option active.
                                    Deactivate
                                    <?php echo $trex_api_strategy->getName() ?> and this option will be enabled.
                                </div>
                            </div>
                        <?php } else { ?>
                            <a href="https://wpml.org/purchase" target="_new">Buy & Download</a>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>

    </div>


    <div class="plugin">
        <div>
            <img src="<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/plugins/polylang.png" ?>"
                 class="logo">
        </div>
        <div class="title">
            <a onclick="openLightBox('/wp-admin/plugin-install.php?tab=plugin-information&plugin=polylang&TB_iframe=true'); return false;"
               href="#">Polylang</a>
        </div>
        <div class="description">
            Making WordPress multilingual
        </div>

        <div class="by">
            By <a href="https://polylang.pro/">Frédéric Demarle</a>
        </div>

        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="text-align:left">
                        <a onclick="showInstructions('Polylang', '<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/plugins/polylang.png" ?>', 'k073eqiltew'); return false;" href="#">Watch Video</a>
                    </td>
                    <td style="text-align:right">
                        <?php if ($trex_api_strategy->getName() === 'polylang') { ?>
                            <strong>Active</strong>
                        <?php } elseif ($trex_api_strategy->getName() !== 'default') { ?>
                            <div class="help" style="display: inline-block; margin-left: 10px;">
                                ?
                                <div class="tooltip">
                                    This option is not available while you have at least one other option active.
                                    Deactivate
                                    <?php echo $trex_api_strategy->getName() ?> and this option will be enabled.
                                </div>
                            </div>
                        <?php } else { ?>
                            <a href="<?php echo create_plugin_link('polylang') ?>">Install & Activate</a>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>

    </div>

    <div class="plugin">
        <div>
            <img
                src="<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/plugins/qtranslate-x.png" ?>"
                class="logo">
        </div>
        <div class="title">
            <a onclick="openLightBox('/wp-admin/plugin-install.php?tab=plugin-information&plugin=qtranslate-x&TB_iframe=true'); return false;"
               href="#">qTranslate X</a>
        </div>
        <div class="description">
            Adds a user-friendly multilingual dynamic content management.
        </div>
        <div class="by">
            By <a href="https://qtranslatexteam.wordpress.com/about/">qTranslate Team</a>
        </div>

        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="text-align:left">
                        <a onclick="showInstructions('qTranslate', '<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/plugins/qtranslate-x.png" ?>', 'tTlmsqOjsRY'); return false;" href="#">Watch Video</a>
                    </td>
                    <td style="text-align:right">
                        <?php if ($trex_api_strategy->getName() === 'qtranslate') { ?>
                            <strong>Active</strong>
                        <?php } elseif ($trex_api_strategy->getName() !== 'default') { ?>
                            <div class="help" style="display: inline-block; margin-left: 10px;">
                                ?
                                <div class="tooltip">
                                    This option is not available while you have at least one other option active.
                                    Deactivate
                                    <?php echo $trex_api_strategy->getName() ?> and this option will be enabled.
                                </div>
                            </div>
                        <?php } else { ?>
                            <a href="<?php echo create_plugin_link('qtranslate-x') ?>">Install & Activate</a>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div style="width: 600px; margin: auto; margin-top: 40px;">
        <?php include_once dirname(__FILE__) . "/instructions/contact.php"; ?>
    </div>

</div>