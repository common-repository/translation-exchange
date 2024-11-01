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

require_once(dirname(__FILE__) . '/../helpers/po_to_mo.php');

class SystemManager
{
    var $files;
    var $plural_forms;

    /**
     * Generate pagination structure
     *
     * @param $page
     * @param $per_page
     * @param $total_count
     * @return array
     */
    public function pagination($page, $per_page, $total_count)
    {
        $total_pages = round($total_count / $per_page);
        if ($total_count % $per_page > 0)
            $total_pages = $total_pages + 1;

        return array(
            'page' => $page,
            'per_page' => $per_page,
            'total_count' => $total_count,
            'total_pages' => $total_pages
        );
    }

    /**
     * Get manager type
     *
     * @return string
     */
    public function getType() {
        return 'system';
    }

    /**
     * Get manager title
     *
     * @return string
     */
    public function getTitle() {
        return 'System';
    }

    /**
     * Get items as a map
     *
     * @return array
     */
    public function getItemMap() {
        return array();
    }

    /**
     * Get a list of items
     * @param $params
     * @return array
     */
    public function getItems($params)
    {
        $items = array();

        foreach ($this->getItemMap() as $key => $item) {
            array_push($items, $item);
        }

        $results = array(
            "results" => $items,
            "pagination" => $this->pagination(1, 30, count($items))
        );

        return $results;
    }

    /**
     * Returns an item
     *
     * @param $params
     * @return array
     */
    public function getItem($params)
    {
        $map = $this->getItemMap();
        if (!isset($map[$params['key']])) {
            return array("status" => "error", "message" => $this->getTitle() . " not found");
        }

        return $map[$params['key']];
    }

    /**
     * Get full item path
     *
     * @param $path
     * @return mixed
     */
    function getFullPath($path) {
        return $path;
    }

    /**
     * @return string
     */
    function getLanguagesPath() {
        return ABSPATH . "wp-content/languages";
    }

    /**
     * Returns plural forms based on locales
     *
     * @param $locale
     * @return mixed
     */
    public function getPluralForms($locale = null) {
        if (!$this->plural_forms) {
            $this->plural_forms = json_decode(file_get_contents(dirname(__FILE__) . '/mapping/plural_forms.json'), true);
        }

        if ($locale) {
            if (isset($this->plural_forms[$locale]))
                return $this->plural_forms[$locale]['plural_forms'];
            return null;
        }

        return $this->plural_forms;
    }

    /**
     * Get templates
     *
     * @param $params
     * @return array
     */
    function getTemplate($params)
    {
        $map = $this->getItemMap();
        if (!isset($map[$params['key']])) {
            return array("status" => "error", "message" => $this->getTitle() . " not found");
        }

        $item = $map[$params['key']];
        $path = $this->getFullPath($item['path']);

        $this->files = array();
        $this->listFiles($path, '/\.pot$/');

        if (count($this->files) == 0 || isset($params['reset'])) {
            $messages = $this->extractFolder($path);

            $dir = $path . '/i18n';
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $file_path = $dir . '/' . $item['key'] . '.pot';
            error_log("Saving file to " . $file_path);
            $this->exportFile($item, $file_path, $messages);
        } else {
            $file_path = $this->files[0];
        }

        $relative = str_replace($path, '', $file_path);
        $content = file_get_contents($file_path);
        $count = substr_count($content, "msgid ");

        return array(
            "path" => $relative,
            "content" => $content,
            "count" => $count
        );
    }

    /**
     * Returns translations map
     *
     * @param $key
     * @return array
     */
    function getTranslationsMap($key)
    {
        $map = $this->getItemMap();
        if (!isset($map[$key])) {
            return array("status" => "error", "message" => $this->getTitle() . " not found");
        }

        $item = $map[$key];
        $path = $this->getFullPath($item['path']);

        $locations = array($path, $this->getLanguagesPath());

        $trex_api_strategy = new DefaultStrategy();

        $files = array();
        foreach($locations as $location) {
            $this->files = array();
            $this->listFiles($location, '/' . $item['key'] . '-[^\.]*\.po$/');

            foreach ($this->files as $file) {
                $relative = str_replace(ABSPATH . "wp-content", '', $file);
                error_log($relative);

                $parts = explode("-", $relative);
                $locale = 'unknown';

                if (count($parts) > 1) {
                    $locale = $trex_api_strategy->getExternalLocale(str_replace(".po", "", $parts[1]));
                }

                if (!isset($files[$locale])) {
                    $files[$locale] = array();
                }

                array_push($files[$locale], array(
                    "path" => $relative,
                    "count" => substr_count(file_get_contents($file), "msgid ")
                ));
            }
        }

        return $files;
    }

    /**
     * Get a list of translation files
     *
     * @param $params
     * @return array
     */
    function getTranslations($params)
    {
        $map = $this->getTranslationsMap($params['key']);

        $results = array();

        if (isset($params['locale'])) {
            $locale = $params['locale'];

            if (!isset($map[$locale])) {
                return array("status" => "error", "message" => "Translations for locale " . $params['locale'] . " are not found");
            }

            $files = $map[$params['locale']];
            foreach ($files as $file) {
                $path = ABSPATH . "wp-content" . $file["path"];
                if (!file_exists($path))
                    continue;
                $content = file_get_contents($path);

                array_push($results, array(
                    "locale" => $locale,
                    "file" => $file["path"],
                    "content" => $content
                ));
            }
        } else {
            foreach ($map as $locale => $files) {
                array_push($results, array("locale" => $locale, "files" => $files));
            }
        }

        return array("results" => $results);
    }


    /**
     * Save translations
     *
     * @param $params
     * @return array
     */
    function postTranslations($params)
    {
        $map = $this->getItemMap();
        if (!isset($map[$params['key']])) {
            return array("status" => "error", "message" => $this->getTitle() . " not found");
        }

        if (!isset($params['locale'])) {
            return array("status" => "error", "message" => "Locale must be provided");
        }

        if (!isset($params['content'])) {
            return array("status" => "error", "message" => "Content must be provided");
        }

        $external_locale = $params['locale'];
        $trex_api_strategy = new DefaultStrategy();
        $internal_locale = $trex_api_strategy->getInternalLocale($external_locale);

        if ($internal_locale === null) {
            return array("status" => "error", "message" => "Locale " . $external_locale . " not supported");
        }

        $item = $map[$params['key']];
        $path = $this->getLanguagesPath() . "/" . $item["key"] . "-" . $internal_locale . ".po";

        $file = fopen($path, "w");
        fwrite($file, $params['content']);
        fclose($file);

        trex_po_to_mo($path);

        return $this->getTranslations(array("key" => $params['key']));
    }

    /**
     * Sanitize label
     *
     * @param $label
     * @return mixed
     */
    function sanitizeLabel($label)
    {
        $result = preg_replace('/^[\'"]/', '', $label);
        $result = preg_replace('/[\'"]$/', '', $result);
        return $result;
    }

    /**
     * Extract localized strings
     *
     * @param $file
     * @return array|null
     */
    function extractLocalizedStrings($file)
    {
        $expressions = array(
            "label" => array(
                "pattern" => '/(__|_e|esc_attr__|esc_attr_e)\s*\(\s*(\'[^\']*\'|"[^"]*")\s*(,\s*(\'[^\']*\'|"[^"]*"|\$[a-zA-Z]+\d*)\s*)?\)/',
                "extract" => function ($matches) {
                    $results = array();
                    for ($index = 0; $index < count($matches[0]); $index++) {
                        $label = $this->sanitizeLabel($matches[2][$index]);
                        $domain = $this->sanitizeLabel($matches[4][$index]);
                        array_push($results, array("label" => $label, "domain" => $domain));
                    }
                    return $results;
                }
            ),
            "label_with_context" => array(
                "pattern" => '/(_x|_ex)\s*\(\s*(\'[^\']*\'|"[^"]*")\s*,\s*(\'[^\']*\'|"[^"]*")\s*(,\s*(\'[^\']*\'|"[^"]*"|\$[a-zA-Z]+\d*)\s*)?\)/',
                "extract" => function ($matches) {
                    $results = array();
                    for ($index = 0; $index < count($matches[0]); $index++) {
                        $label = $this->sanitizeLabel($matches[2][$index]);
                        $context = $this->sanitizeLabel($matches[3][$index]);
                        $domain = $this->sanitizeLabel($matches[5][$index]);
                        array_push($results, array("label" => $label, "context" => $context, "domain" => $domain));
                    }
                    return $results;
                }
            ),

            "plural" => array(
                "pattern" => '/(_n)\s*\(\s*(\'[^\']*\'|"[^"]*")\s*,\s*(\'[^\']*\'|"[^"]*")\s*,[^,]*(,\s*(\'[^\']*\'|"[^"]*"|\$[a-zA-Z]+\d*)\s*)?\)/',
                "extract" => function ($matches) {
                    $results = array();
                    for ($index = 0; $index < count($matches[0]); $index++) {
                        $labels = array();
                        array_push($labels, $this->sanitizeLabel($matches[2][$index]));
                        array_push($labels, $this->sanitizeLabel($matches[3][$index]));
                        $domain = $this->sanitizeLabel($matches[5][$index]);
                        array_push($results, array("plural" => true, "labels" => $labels, "domain" => $domain));
                    }
                    return $results;
                }
            ),

            "plural_with_context" => array(
                "pattern" => '/(_nx)\s*\(\s*(\'[^\']*\'|"[^"]*")\s*,\s*(\'[^\']*\'|"[^"]*")\s*,[^,]*,\s*(\'[^\']*\'|"[^"]*")\s*(,\s*(\'[^\']*\'|"[^"]*"|\$[a-zA-Z]+\d*)\s*)?\)/',
                "extract" => function ($matches) {
                    $results = array();
                    for ($index = 0; $index < count($matches[0]); $index++) {
                        $labels = array();
                        array_push($labels, $this->sanitizeLabel($matches[2][$index]));
                        array_push($labels, $this->sanitizeLabel($matches[3][$index]));
                        $context = $this->sanitizeLabel($matches[4][$index]);
                        $domain = $this->sanitizeLabel($matches[6][$index]);
                        array_push($results, array("plural" => true, "labels" => $labels, "context" => $context, "domain" => $domain));
                    }
                    return $results;
                }
            )
        );

        $handle = fopen($file, "r");
        if (!$handle) return null;

        $file_matches = array(
            "path" => $file,
            "matches" => array()
        );

        $line_index = 1;
        while (($line = fgets($handle)) !== false) {
            foreach ($expressions as $key => $expr) {
                preg_match_all($expr['pattern'], $line, $matches);

                if (count($matches[0]) == 0) {
                    continue;
                }

//                error_log($line);
//                error_log(var_export($matches, true));

                $results = $expr['extract']($matches);
                array_push($file_matches['matches'], array("line" => $line_index, "results" => $results));
            }
            $line_index++;
        }
        fclose($handle);

        return $file_matches;
    }

    /**
     * Extract folder
     *
     * @param $path
     * @return array
     */
    function extractFolder($path) {
        $this->files = array();
        $this->listFiles($path, '/\.php$/');

        $matches_by_file = array();
        foreach ($this->files as $file) {
            $file_matches = $this->extractLocalizedStrings($file);
            array_push($matches_by_file, $file_matches);
        }

        $messages = array();
        foreach ($matches_by_file as $file_matches) {
            $relative_path = str_replace($path . '/', '', $file_matches['path']);

            foreach ($file_matches['matches'] as $match) {
                $location = array(
                    "file" => $relative_path,
                    "line" => $match["line"]
                );
                foreach ($match['results'] as $result) {
                    if (isset($result['plural'])) {
                        $key = implode(':::', $result['labels']);
                    } elseif (isset($result['context'])) {
                        $key = $result['label'] . ':::' . $result['context'];
                    } else {
                        $key = $result['label'];
                    }

                    if (!isset($messages[$key])) {
                        $messages[$key] = array_merge(array(), $result);
                        $messages[$key]["locations"] = array();
                    }

                    array_push($messages[$key]["locations"], $location);
                }
            }
        }

        return array_values($messages);
    }

    /**
     * Write a line into a file
     *
     * @param $file
     * @param $line
     */
    function writeLn($file, $line)
    {
        fwrite($file, $line . "\n");
    }

    /**
     * Export to file
     *
     * @param $item
     * @param $path
     * @param $messages
     */
    function exportFile($item, $path, $messages)
    {
//        error_log(var_export($item, true));

        global $trex_api_strategy;
        $default_locale = $trex_api_strategy->getDefaultLocale();

        $author = isset($item["author"]) ? $item["author"] : '';
        $author_uri = isset($item["author_uri"]) ? $item["author_uri"] : '';
        $name = isset($item["name"]) ? $item["name"] : '';
        $version = isset($item["version"]) ? $item["version"] : '';
        $plural_forms = $this->getPluralForms($default_locale);

        $file = fopen($path, "w");

        $this->writeLn($file, "# Copyright (C) 2017 " . $author . " " . $author_uri);
        $this->writeLn($file, "# This file is distributed under the same license as the package.");
        $this->writeLn($file, 'msgid ""');
        $this->writeLn($file, 'msgstr ""');
        $this->writeLn($file, '"Project-Id-Version: ' . $name . ' ' . $version . '\n"');
        $this->writeLn($file, '"Report-Msgid-Bugs-To: \n"');
        $this->writeLn($file, '"POT-Creation-Date: ' . date('Y-m-d H:i:s', time()) . '\n"');
        $this->writeLn($file, '"MIME-Version: 1.0\n"');
        $this->writeLn($file, '"Content-Type: text/plain; charset=utf-8\n"');
        $this->writeLn($file, '"Content-Transfer-Encoding: 8bit\n"');

        if ($plural_forms) {
            $this->writeLn($file, '"Plural-Forms: ' . $plural_forms . '\n"');
        }

        $this->writeLn($file, '"PO-Revision-Date: 2017-MO-DA HO:MI+ZONE\n"');
        $this->writeLn($file, '"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"');
        $this->writeLn($file, '"Language-Team: LANGUAGE <EMAIL@ADDRESS>\n"');
        $this->writeLn($file, '"X-Generator: translation-exchange 1.0.3\n"');
        $this->writeLn($file, "");

        foreach ($messages as $message) {
            if (isset($message['locations'])) {
                foreach ($message['locations'] as $location) {
                    $this->writeLn($file, '#: ' . $location['file'] . ':' . $location['line']);
                }
            }

            if (isset($message['context'])) {
                $this->writeLn($file, 'msgctxt "' . str_replace('"', '\"', $message['context']) . '"');
            }

            if (isset($message['plural'])) {
                $this->writeLn($file, 'msgid "' . str_replace('"', '\"', $message['labels'][0]) . '"');
                $this->writeLn($file, 'msgid_plural "' . str_replace('"', '\"', $message['labels'][1]) . '"');
                for ($i = 0; $i < count($message['labels']); $i++) {
                    $this->writeLn($file, 'msgstr[' . $i . '] ""');
                }
            } else {
                $this->writeLn($file, 'msgid "' . str_replace('"', '\"', $message['label']) . '"');
                $this->writeLn($file, 'msgstr ""');
            }

            $this->writeLn($file, "");
        }

        fclose($file);
    }

    /**
     * Recursively finds files in a folder
     *
     * @param $dir
     * @param $ext
     */
    function listFiles($dir, $ext)
    {
        $ffs = scandir($dir);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1)
            return;

        foreach ($ffs as $ff) {
            $path = $dir . '/' . $ff;
            if (is_dir($path))
                $this->listFiles($dir . '/' . $ff, $ext);
            else {
                if (!preg_match($ext, $path))
                    continue;

                array_push($this->files, $path);
            }
        }
    }

}