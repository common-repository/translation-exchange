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

class PluginManager extends SystemManager
{

    public function getType()
    {
        return 'plugin';
    }

    public function getTitle()
    {
        return 'Plugin';
    }

    /**
     * Returns a map of plugins by key
     *
     * @return array
     */
    public function getItemMap()
    {
        $plugins = array();

        foreach (get_plugins() as $path => $plugin) {
            error_log(var_export($plugin, true));

            $id = explode('/', $path)[0];

            $plugins[$id] = array(
                'key' => $id,
                "path" => $path,
                'name' => isset($plugin['Name']) ? $plugin['Name'] : 'Unknown',
                'plugin_uri' => isset($plugin['PluginURI']) ? $plugin['PluginURI'] : 'Unknown',
                'version' => isset($plugin['Version']) ? $plugin['Version'] : 'Unknown',
                'description' => isset($plugin['Description']) ? $plugin['Description'] : 'Unknown',
                'author' => isset($plugin['Author']) ? $plugin['Author'] : 'Unknown',
                'author_uri' => isset($plugin['AuthorURI']) ? $plugin['AuthorURI'] : 'Unknown',
                'text_domain' => isset($plugin['TextDomain']) ? $plugin['TextDomain'] : 'Unknown',
                'domain_path' => isset($plugin['DomainPath']) ? $plugin['DomainPath'] : 'Unknown',
                'network' => isset($plugin['Network']) ? $plugin['Network'] : 'Unknown',
                'title' => isset($plugin['Title']) ? $plugin['Title'] : 'Unknown',
                'author_name' => isset($plugin['AuthorName']) ? $plugin['AuthorName'] : 'Unknown'
            );
        }

        return $plugins;
    }

    /**
     * Return base path of the plugin
     *
     * @param $path
     * @return string
     */
    public function getFullPath($path)
    {
        return dirname(ABSPATH . "wp-content/plugins/" . $path);
    }

    /**
     * @return string
     */
    function getLanguagesPath() {
        return ABSPATH . "wp-content/languages/plugins";
    }
}