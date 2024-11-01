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


require_once dirname(__FILE__) . "/../common/cdn_helpers.php";
require_once dirname(__FILE__) . "/../common/file_utils.php";
require_once dirname(__FILE__) . "/../common/form_helpers.php";

function is_permalink_structure_a_query(){
    $permalink_structure = get_option('permalink_structure');
    if (empty($permalink_structure)) return true;
    if (strpos($permalink_structure, '?')!==false) return true;
    return strpos($permalink_structure, 'index.php')!==false;
}

$post_action = isset($_POST['action']) ? $_POST['action'] : null;

if ($post_action == 'sync_cache') {

    echo "<div class='updated'><p><strong>" . __('Cache version has been updated to the current release version from Translation Exchange.') . "</strong></p></div>";

} elseif ($post_action == 'delete_cache') {

    $version_path = get_option('tml_cache_path') . "/" . $_POST['version'];
    FileUtils::rrmdir($version_path);

    include dirname(__FILE__) . "/settings.php";

} elseif ($post_action == 'use_cache') {

    update_option("tml_cache_type", $_POST['type']);

    if (isset($_POST['version_check_interval']) && $_POST['version_check_interval'] !== '')
        update_option("tml_cache_version_check_interval", $_POST['version_check_interval']);

    if (isset($_POST['version']) && $_POST['version'] !== '')
        update_option("tml_cache_version", $_POST['version']);

    include dirname(__FILE__) . "/settings.php";

} elseif ($post_action == 'download_cache') {

    include dirname(__FILE__) . "/cache_options/download_snapshot.php";

} else { // snapshot generation end

    include dirname(__FILE__) . "/settings.php";
}
