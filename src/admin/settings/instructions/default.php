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

<div style="background: white; border: 1px solid #ccc; padding: 10px; border-radius: 5px; text-align: center">
    <div style="text-align: center">
        <img
            src="<?php echo plugin_dir_url(__FILE__) . "../../../../assets/images/plugins/translationexchange.png" ?>"
            class="logo">

        <h1>Translation Exchange Plugin Instructions</h1>

        <div style="margin-top: 20px;margin-bottom: 20px;">
            <div class="screen">
                <iframe class="youtube" width="560" height="315" src="https://www.youtube.com/embed/7u2bnE54TUk"
                        frameborder="0"
                        allowfullscreen></iframe>
            </div>
        </div>
    </div>


    <div style="width: 600px; margin: auto; text-align: left">
        <table>
            <tr>
                <td>
                    <div class="inst-dot">1</div>
                </td>
                <td>
                    <div class="inst-title">Create an account</div>
                    <div class="inst-info">Register with Translation Exchange and <a
                            href="https://dashboard.translationexchange.com">create your account</a>.
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="inst-dot">2</div>
                </td>
                <td>
                    <div class="inst-title">Create a project</div>
                    <div class="inst-info">Walk through the steps of creating your first project. It's easy!
                        Copy the project key from your dashboard and paste it into the plugin.
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="inst-dot">3</div>
                </td>
                <td>
                    <div class="inst-title">Add language selector</div>
                    <div class="inst-info">
                        Open the <a href="<?php echo admin_url('widgets.php') ?>">widgets section</a> and drag the
                        "Language Selector" into the "Sidebar".
                        There are a number of options you can choose from. You can also enable Translation Mode toggle
                        button, if you want to allow crowd-sourcing.
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="inst-dot">4</div>
                </td>
                <td>
                    <div class="inst-title">Activate translation mode</div>
                    <div class="inst-info">
                        Translation Exchange only sends your site content to the server when translation mode is
                        activated.

                        You can activate translation mode either from the language selector (if you enabled it) or using
                        keyboard
                        shortcut keys:
                        <br><br>

                        On Windows: <kbd>CTRL</kbd> + <kbd>Shift</kbd> + <kbd>i</kbd>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        On Mac: <kbd>CMD</kbd> + <kbd>Shift</kbd> + <kbd>i</kbd>

                        <hr>
                        When translation mode is activated, all the strings on your site will be identified with
                        colored underlines. The colors carry the following meanings:<br><br>

                        <table>
                            <tr>
                                <td><span style="border-bottom: 2px solid orange">Orange</span></td>
                                <td>- A new string has been detected and is being sent to the server.</td>
                            </tr>
                            <tr>
                                <td><span style="border-bottom: 2px solid red">Red</span></td>
                                <td>- No translations have been found.</td>
                            </tr>
                            <tr>
                                <td><span style="border-bottom: 2px solid green">Green</span></td>
                                <td>- Translations are available, but need to be reviewed.</td>
                            </tr>
                            <tr>
                                <td><span style="border-bottom: 2px solid blue">Blue</span></td>
                                <td>- Translations have been reviewed and approved.</td>
                            </tr>
                        </table>

                        <hr>

                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="inst-dot">5</div>
                </td>
                <td>
                    <div class="inst-title">Translate content</div>
                    <div class="inst-info">
                        Translation mode allows you to right-mouse-click on any string to translate it in-context.
                        <br><br>
                        You can also translate your content using machines, your own translators, crowd-sourcing or our translation
                        marketplace.
                        Press the "Order Translations" button and choose the provider and languages you want to order.
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="inst-dot">6</div>
                </td>
                <td>
                    <div class="inst-title">Publish translations</div>
                    <div class="inst-info">
                        Once your content is translated, select the Releases section and click on the Publish button.
                        This will create a translation bundle and deploy it to our Content Delivery Network (CDN).
                        You can let us host the translations or download and host them yourself using this plugin.
                        <br><br>
                        If you released something by mistake, no worries, simply select a different release version in the
                        dashboard and the translations will be rolled back to the previous release.

                    </div>
                </td>
            </tr>
        </table>

        <div style="text-align: center; padding: 20px;">
            <button class="button-primary" onclick="toggleInstructions()">Got it, Thank you!</button>
        </div>
        <?php include_once dirname(__FILE__) . "/contact.php"; ?>
    </div>

</div>
