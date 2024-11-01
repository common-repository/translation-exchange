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

<?php include_once dirname(__FILE__) . "/check_api.php"; ?>

<h2>
    <img src="<?php echo plugin_dir_url(__FILE__) . "../../../../assets/images/logo.png" ?>"
         style="width: 40px; vertical-align:middle; margin: 0px 5px; border-radius: 5px;">
    <?php echo __('Translation Exchange with WPML'); ?>

    <div style="float:right; font-size: 14px; padding-top: 6px;">
        <a href="#" onclick="showHowItWorks(); return false;">How does it work?</a>
        |
        <a href="<?php echo admin_url('options-general.php?page=trex-settings&options=true') ?>">Other Options</a>
    </div>
</h2>

<hr/>

<div class="info" style="background: white; text-align:center">
    <div>
        <img
            src="<?php echo plugin_dir_url(__FILE__) . "../../../../assets/images/confirmations/wpml.png" ?>"
            >
    </div>

    <div style="color: #666; font-size: 18px; padding: 10px;">
        Congratulations!
    </div>

    <div style="padding-top: 10px;">
        Translation Exchange has been successfully configured to be used with your WPML plugin. <br>
        No further configuration is needed. Please see the instructions below on how to import, translate and publish your translated posts and pages using Translation
        Exchange platform.
    </div>
</div>

<div style="background: white; padding: 10px; border-radius: 5px; text-align: center">
    <div style="text-align: center">
        <img
            src="<?php echo plugin_dir_url(__FILE__) . "../../../../assets/images/plugins/wpml.png" ?>"
            class="logo">

        <h3 style="color: #666">See WPML and Translation Exchange in action in the video below.</h3>

        <div style="margin-top: 20px;margin-bottom: 20px;">
            <div class="screen">
                <iframe class="youtube" width="560" height="315" src="https://www.youtube.com/embed/qgDb1nfPklE"
                        frameborder="0"
                        allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 25px;">
        <h1>Translation Exchange Instructions</h1>
    </div>

    <?php include_once dirname(__FILE__) . "/common.php"; ?>

</div>
