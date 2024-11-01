<?php

/*
  Copyright (c) 2017 Translation Exchange, Inc. https://translationexchange.com

   _______                  _       _   _             ______          _
  |__   __|                | |     | | (_)           |  ____|        | |
     | |_ __ __ _ _ __  ___| | __ _| |_ _  ___  _ __ | |__  __  _____| |__   __ _ _ __   __ _  ___
     | | '__/ _` | '_ \/ __| |/ _` | __| |/ _ \| '_ \|  __| \ \/ / __| '_ \ / _` | '_ \ / _` |/ _ \
     | | | | (_| | | | \__ \ | (_| | |_| | (_) | | | | |____ >  < (__| | | | (_| | | | | (_| |  __/
     |_|_|  \__,_|_| |_|___/_|\__,_|\__|_|\___/|_| |_|______/_/\_\___|_| |_|\__,_|_| |_|\__, |\___|
                                                                                         __/ |
                                                                                        |___/
    GNU General Public License, version 2

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

    http://www.gnu.org/licenses/gpl-2.0.html
*/

function is_permalink_structure_a_query(){
    $permalink_structure = get_option('permalink_structure');
    if (empty($permalink_structure)) return true;
    if (strpos($permalink_structure, '?')!==false) return true;
    return strpos($permalink_structure, 'index.php')!==false;
}

function getCdnHost()
{
    $cdn_host = "https://cdn.translationexchange.com";

    $agent_options = stripcslashes(get_option('tml_agent_options'));

    if ($agent_options === '') {
        return $cdn_host;
    }

    $custom_host = null;
    try {
        $data = json_decode($agent_options, true);
        $custom_host = isset($data['cdn_host']) ? $data['cdn_host'] : null;
    } catch (Exception $e) {
        $custom_host = null;
    }

    if ($custom_host != null)
        return $custom_host;

    return $cdn_host;
}

function fetchFromCdn($path, $opts = array())
{
    try {
        $curl_handle = curl_init();

        if (substr( $path, 0, 1 ) != '/')
            $path = '/' . $path;

        $url = getCdnHost() . $path;
//        echo "Fetching from " . $url;

        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl_handle);
        curl_close($curl_handle);

        if (isset($opts['decode']) && $opts['decode'])
            $data = json_decode($data, true);
    } catch (Exception $e) {
        $data = false;
    }

    return $data;
}
