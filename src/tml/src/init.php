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
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/** Enabble Following lines in Debug mode only  **/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

/**
 * Require files in a specific order
 */
$files = array(
    "Tml/Utils",
    "Tml/Base.php",
    "Tml",
    "Tml/Api",
    "Tml/Tokens",
    "Tml/Tokenizers",
    "Tml/RulesEngine",
    "Tml/Decorators/Base.php",
    "Tml/Decorators",
    "Tml/Cache/Base.php",
    "Tml/Cache",
    "Tml/Includes/Tags.php"
);

foreach($files as $dir) {
    $path = dirname(__FILE__)."/".$dir;
    if (is_dir($path)) {
        foreach (scandir($path) as $filename) {
            $file = $path . "/" . $filename;
            if (is_file($file)) {
                require_once $file;
            }
        }
    } else {
        require_once $path;
    }
}

use Tml\Config;
use Tml\Session;

/**
 * Initializes the TML library
 *
 * @param null $token
 * @param array $options
 * @return bool
 */
function tml_init($options = array()) {
    return Session::init($options);
}

/**
 * @param array $options
 */
function tml_complete_request($options = array()) {
    Session::finalize($options);
}

/**
 * Includes Tml JavaScript library
 */
function tml_scripts() {
    Config::instance()->scripts();
}

/**
 * Includes Tml footer scripts
 */
function tml_footer() {
    Config::instance()->footer();
}

/**
 * @return \Tml\Application
 */
function tml_application() {
    return Session::application();
}

/**
 * @return \Tml\Language
 */
function tml_current_locale() {
    return Session::currentLocale();
}

/**
 * @return \Tml\Language
 */
function tml_current_language() {
    return Session::currentLanguage();
}

/**
 * @return string
 */
function tml_current_language_direction() {
    return Session::currentLanguageDirection();
}

/**
 * @return \Tml\Translator
 */
function tml_current_translator() {
    return Session::currentTranslator();
}

/**
 * @return null|\Tml\Source
 */
function tml_current_source() {
    return Session::instance()->current_source;
}

/**
 * Opens the source block
 *
 * @param string $name
 */
function tml_begin_source($name) {
    Session::instance()->beginSource($name);
}

/**
 * Closes the source block
 */
function tml_finish_source() {
    Session::instance()->finishSource();
}

/**
 * @param array $options
 */
function tml_begin_block_with_options($options = array()) {
    Session::instance()->beginBlockWithOptions($options);
}

/**
 * @return null
 */
function tml_finish_block_with_options() {
    Session::instance()->finishBlockWithOptions();
}

/**
 * There are three ways to call this method:
 *
 * 1. tr($label, $description = "", $tokens = array(), options = array())
 * 2. tr($label, $tokens = array(), $options = array())
 * 3. tr($params = array("label" => label, "description" => "", "tokens" => array(), "options" => array()))
 *
 * @param string $label
 * @param string $description
 * @param array $tokens
 * @param array $options
 * @throws Exception
 * @return mixed
 */
function tr($label, $description = "", $tokens = array(), $options = array()) {
    return Session::tr($label, $description, $tokens, $options);
}

/**
 * Translates a label and prints it to the page
 *
 * @param string $label
 * @param string $description
 * @param array $tokens
 * @param array $options
 */
function tre($label, $description = "", $tokens = array(), $options = array()) {
    Session::tre($label, $description, $tokens, $options);
}

/**
 * Translates a label while suppressing its decorations
 * The method is useful for translating alt tags, list options, etc...
 *
 * @param string $label
 * @param string $description
 * @param array $tokens
 * @param array $options
 * @return mixed
 */
function trl($label, $description = "", $tokens = array(), $options = array()) {
	return Session::trl($label, $description, $tokens, $options);
}

/**
 * Same as trl, but with printing it to the page
 *
 * @param string $label
 * @param string $description
 * @param array $tokens
 * @param array $options
 */
function trle($label, $description = "", $tokens = array(), $options = array()) {
    Session::trl($label, $description, $tokens, $options);
}

/**
 * Translates a block of html, converts it to TML, translates it and then converts it back to HTML
 *
 * @param $label
 * @param string $description
 * @param array $tokens
 * @param array $options
 * @return array
 */
function trh($label, $description = "", $tokens = array(), $options = array()) {
    return Session::trh($label, $description, $tokens, $options);
}

/**
 * Translates a block of html, converts it to TML, translates it and then converts it back to HTML
 *
 * @param $label
 * @param string $description
 * @param array $tokens
 * @param array $options
 */
function trhe($label, $description = "", $tokens = array(), $options = array()) {
    Session::trhe($label, $description, $tokens, $options);
}
