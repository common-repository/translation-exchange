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


$submit_field_name = 'tml_submit_hidden';
$cache_field_name = 'tml_update_cache_hidden';

$application_fields = array(
    'tml_key' => array(
        "title" => __('Project Key:'),
        "value" => get_option('tml_key'),
        "default" => __("Paste your application key here"),
        "help" => __("Project key uniquely identifies your application. Please visit the project integration instructions under your dashboard to get the key.")
    ),

    'separator' => true,

    'tml_locale_selector' => array(
        "title" => __('Language Detection:'),
        "value" => get_option('tml_locale_selector', 'param'),
        "default" => "param",
        "type" => "radio",
        "separator" => "<div style='height: 5px;'></div>",
        "options" => array(
            array(
                "title" => __("Use query parameter. <i class='mini-info'>For example, '?locale=en'. Not recommended, not SEO friendly.</i>"),
                "value" => "param"
            ),
            array(
                "title" => __("Use pre-path element. <i class='mini-info'>For example, adds /en/ in front of URL. SEO friendly.</i>"),
                "value" => "pre-path",
                "disabled" => is_permalink_structure_a_query()
            ),
            array(
                "title" => __("Use domain prefix. <i class='mini-info'>For example, http://en.yoursite.com. Additional DNS sub-domain configuration is required.</i>"),
                "value" => "pre-domain"
            )
        ))
);

$script_fields = array(
    'tml_script_host' => array(
        "title" => __('Script Host:'),
        "value" => get_option('tml_script_host'),
        "type" => "text",
        "default" => "https://cdn.translationexchange.com/tools/tml/stable/tml.min.js",
        "style" => "display:none",
        "help" => __("This is an advanced option. Paste an alternative URL for the tml.js script here. You can provide a specific version of the script based on the script release version.")
    ),
    'tml_script_options' => array(
        "title" => __('Options:'),
        "value" => get_option('tml_script_options'),
        "type" => "textarea",
        "default" => __('Provide custom script options in JSON format'),
        "style" => "display:none",
        "help" => __("This is an advanced option. Provide any additional custom options for the initialization instructions of the TML agent.")
    ),
);

$field_sets = array($application_fields, $script_fields);

if (isset($_POST[$submit_field_name]) && $_POST[$submit_field_name] == 'Y') {
    $index = 0;
    foreach ($field_sets as $set) {
        foreach ($set as $key => $attributes) {
            if ($key == 'separator') continue;
            update_option($key, $_POST[$key]);
            $field_sets[$index][$key] = array_merge($attributes, array("value" => $_POST[$key]));
        }
        $index++;
    }

    if (get_option("tml_cache_type") == "dynamic")
        update_option("tml_cache_type", "none");
    ?>

    <div class="updated"><p><strong><?php _e('Settings have been saved.'); ?></strong></p></div>
    <?php
}
?>

<script>
    function toggleInstructions() {
        if (jQuery('#trex_content').is(':visible')) {
            jQuery('#trex_content').hide();
            jQuery('#trex_instructions').show();
            jQuery('#toggler').html('Hide Instructions');
        } else {
            jQuery('#trex_content').show();
            jQuery('#trex_instructions').hide();
            jQuery('#toggler').html('Show Instructions');
        }
    }
</script>
<link rel='stylesheet' href='<?php echo plugin_dir_url(__FILE__) . "../../../assets/css/styles.css" ?>' type='text/css'
      media='all'/>

<div class="wrap">
    <h2>
        <img src="<?php echo plugin_dir_url(__FILE__) . "../../../assets/images/logo.png" ?>"
             style="width: 40px; vertical-align:middle; margin: 0px 5px; border-radius: 5px;">
        <?php echo __('Translation Exchange Settings'); ?>

        <div style="float:right; font-size: 14px; padding-top: 6px;">
            <a href="#" id="toggler" onclick="toggleInstructions(); return false;">Show Instructions</a>
            |
            <a href="<?php echo admin_url('options-general.php?page=trex-settings&options=true') ?>">Other Options</a>
        </div>
    </h2>

    <hr/>

    <div id="trex_content">
        <h2>
            <?php _e('Basic Settings') ?>
        </h2>
        <hr/>

        <div style="background: white; border: 1px solid #ccc; border-radius: 5px; padding: 10px;">

            <p style="color:#888; background: #eee; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
                To get your project key, please visit your
                <a href="https://dashboard.translationexchange.com" target="_new" style="text-decoration: none">
                    Translation Exchange Dashboard
                </a> and choose <strong>Integration Section</strong> from the navigation menu.
            </p>

            <form name="configuration_form" method="post" action="">
                <input type="hidden" name="<?php echo $cache_field_name; ?>" id="<?php echo $cache_field_name; ?>"
                       value="N">
                <input type="hidden" name="<?php echo $submit_field_name; ?>" id="<?php echo $submit_field_name; ?>"
                       value="Y">

                <table style="margin-top: 10px; width: 100%">
                    <?php foreach ($field_sets as $field_set) { ?>
                        <?php foreach ($field_set as $key => $field) {

                            if ($key == 'separator') {
                                echo "<tr><td colspan=\"3\"><hr></td></tr>";
                                continue;
                            }

                            $type = (!isset($field['type']) ? 'text' : $field['type']);
                            $style = (!isset($field['style']) ? '' : $field['style']);
                            ?>

                            <tr style="<?php echo $style ?>" id="<?php echo $key ?>">
                                <td style="padding-left: 10px; width: 150px; vertical-align: top;">
                                    <?php echo($field["title"]) ?>
                                </td>

                                <td style="">
                                    <?php

                                    if ($type == 'text') {

                                        text_field_tag($key, $field["value"], array(
                                            'placeholder' => $field["default"],
                                            'style' => "width:100%;"
                                        ));

                                    } elseif ($type == 'textarea') {

                                        text_area_tag($key, stripcslashes($field["value"]), array(
                                            'placeholder' => $field["default"],
                                            'style' => "width:100%; height: 200px;"
                                        ));

                                    } elseif ($type == 'radio' && isset($field["options"])) {
                                        foreach ($field["options"] as $option) {
                                            radio_button_tag($key, $option["value"], array(
                                                'checked' => ($field["value"] === $option["value"]),
                                                'disabled' => (isset($option["disabled"]) && $option["disabled"]),
                                                'label' => $option["title"]
                                            ));

                                            if (isset($option['help'])) {
                                                help_tag($option['help']);
                                            }

                                            echo($field['separator']);
                                        }
                                    } elseif ($type == 'checkbox') {
                                        $value = $field["value"];

                                        check_box_tag($key, "true", $value === "true");

                                        if (isset($field['notes'])) {
                                            span_tag($field['notes'], "padding-left:15px;color:#666;");
                                        }
                                    } ?>
                                </td>
                                <td style="vertical-align: top">
                                    <?php if (isset($field['help'])) {
                                        help_tag($field['help']);
                                    } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr>
                        <td colspan="3">
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <div style="float:right;">
                        <span style="padding-top:5px;" id="tml_script_options_button">
                            <a href="#" class="button"
                               onclick="showScriptOptions();"><?php _e('Show Advanced Options') ?></a>
                        </span>


                                <?php if (get_option('tml_key') !== null) { ?>
                                    <a class="button"
                                       href='https://dashboard.translationexchange.com/#/projects/<?php echo get_option('tml_key') ?>'
                                       target="_new">
                                        <?php _e('Visit Dashboard') ?>
                                    </a>

                                    <a class="button" href='https://translate.translationexchange.com'>
                                        <?php _e('Visit Translation Center') ?>
                                    </a>
                                <?php } ?>

                            </div>
                            <button class="button-primary">
                                <?php _e('Save Changes') ?>
                            </button>
                        </td>
                    </tr>
                </table>
            </form>

        </div>

        <?php if (get_option("tml_key") && get_option("tml_key") !== '') { ?>

            <div style="padding-top: 25px;">
                <h2>
                    <?php _e('Translation Cache Options'); ?>
                </h2>

                <hr/>

                <div style="background: white; border: 1px solid #ccc; border-radius: 5px; padding: 10px;">

                    <p style="color:#888; background: #eee; padding: 10px; border-radius: 3px; margin-bottom: 10px;">
                        <?php _e("You can use our Content Deliver Network (CDN) for loading your site translations, or you can download the release and host the translations yourself.") ?>
                    </p>

                    <form id="cache_form" method="post" action="">
                        <input type="hidden" name="action" id="cache_action" value="download_cache">
                        <input type="hidden" name="type" id="cache_type" value="">
                        <input type="hidden" name="version_check_interval" id="cache_version_check_interval" value="">
                        <input type="hidden" name="version" id="cache_version" value="">

                        <div style="padding-top:10px; vertical-align: top;">
                            <?php include_once dirname(__FILE__) . "/cache_options/local_cdn.php" ?>
                            <?php include_once dirname(__FILE__) . "/cache_options/snapshots.php" ?>
                        </div>
                    </form>
                </div>

                <?php include_once dirname(__FILE__) . "/cache_options/scripts.php" ?>
            </div>

            <div
                style="color: #888; margin: auto; margin-top: 20px; background: #eee; padding: 10px; text-align: center; width: 500px;">
                <?php _e('If you have any questions, please contact our support:') ?>
                <br><br>

                <a href="mailto: support@translationexchange.com">support@translationexchange.com</a>
            </div>

        <?php } ?>
    </div>

    <div id="trex_instructions" style="display: none">
        <?php include_once dirname(__FILE__) . "/instructions/default.php"; ?>
    </div>

    <?php
    if (get_option('trex-instructions', false) === false) {
        update_option('trex-instructions', true);
        ?>
        <script>
            (function () {
                toggleInstructions();
            })();
        </script>
    <?php } ?>
</div>
