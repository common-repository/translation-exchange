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
<div style="width: 600px; margin: auto; text-align: left">
    <table>
        <tr>
            <td>
                <div class="inst-dot">1</div>
            </td>
            <td>
                <div class="inst-title">Create an account</div>
                <div class="inst-info">Register with Translation Exchange and <a href="https://dashboard.translationexchange.com">create your account</a>.</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="inst-dot">2</div>
            </td>
            <td>
                <div class="inst-title">Create a project</div>
                <div class="inst-info">Walk through the steps of creating your first project. It's easy!</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="inst-dot">3</div>
            </td>
            <td>
                <div class="inst-title">Import posts and pages</div>
                <div class="inst-info">Click on the "Import Sources" button from the "Sources" section
                    and select "WordPress". Enter your WordPress site URL, username and password and click on
                    "Continue".
                    Choose posts, pages, themes and plugins that you would like to translate and click "Import".
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="inst-dot">4</div>
            </td>
            <td>
                <div class="inst-title">Translate content</div>
                <div class="inst-info">
                    Translate your content using machines, your own translators, crowd-sourcing or our translation marketplace.
                    Press the "Order Translations" button and choose the provider and languages you want to order.
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="inst-dot">5</div>
            </td>
            <td>
                <div class="inst-title">Publish translations</div>
                <div class="inst-info">
                    Once your content is translated, click on "Publish" button next to the languages you want to publish
                    or "Publish All" for all languages. Seer your content immediately appear translated on your WordPress site.
                </div>
            </td>
        </tr>
    </table>

    <div class="inst-info" style="padding: 10px; margin-top: 20px;">
        Whenever you change your original content, simply choose <strong>"Update Source"</strong> from the
        source menu, translate it and re-publish it back.
        <br><br>
        You can also setup an <strong>automation workflow</strong>, which will be triggered every
        time you change the original article.
        The workflow will pull in your changes, translate them using the method of your choice, and
        publish them back.
    </div>

    <?php include_once dirname(__FILE__) . "/contact.php"; ?>
</div>