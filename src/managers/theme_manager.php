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

require_once(dirname(__FILE__) . '/system_manager.php');

class ThemeManager extends SystemManager
{

    /**
     * Get manager type
     *
     * @return string
     */
    public function getType()
    {
        return 'theme';
    }

    /**
     * Get manager title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Theme';
    }

    /**
     * Get a map of items
     *
     * @return array
     */
    public function getItemMap()
    {
        $themes = array();

        foreach (wp_get_themes() as $theme) {
            $id = $theme->template;
            $themes[$id] = array(
                "key" => $theme->template,
                "name" => $theme->name,
                "description" => $theme->description,
                "author" => $theme->author,
                "version" => $theme->version,
                "tags" => $theme->tags,
                "path" => $theme->template,
            );
//            error_log(var_export($theme, true));
        }

        return $themes;
    }

    /**
     * Get full path
     *
     * @param $path
     * @return string
     */
    public function getFullPath($path)
    {
        return ABSPATH . "wp-content/themes/" . $path;
    }

    /**
     * @return string
     */
    function getLanguagesPath() {
        return ABSPATH . "wp-content/languages/themes";
    }
}
