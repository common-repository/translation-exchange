<?php

use Tml\Session;
use Tml\Utils\ArrayUtils;
use Tml\Utils\StringUtils;
use Tml\Utils\UrlUtils;

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

/**
 * Displays default language selector
 *
 * @param \Tml\Language $language
 * @param array $opts
 */
function tml_language_name_tag($language = null, $opts = array())
{
    $flag = isset($opts["flag"]) ? $opts["flag"] : true;
    $type = isset($opts["type"]) ? $opts["type"] : 'native';

    if ($language == null) $language = tml_current_language();
    if ($flag) {
        tml_language_flag_tag($language);
        echo " ";
    }

    if ($type == 'native') {
        echo "<span dir='ltr'>" . $language->native_name . "</span>";
    } else if ($type == 'translated') {
        echo "<span dir='ltr'>" . tr($language->english_name) . "</span>";
    } else if ($type == 'both') {
        echo "<span dir='ltr'>" . $language->english_name . "</span>";
        echo " <span dir='ltr' style='font-size:10px;'>" . $language->native_name . "</span>";
    } else {
        echo "<span dir='ltr'>" . $language->english_name . "</span>";
    }
}

/**
 * Displays Power By text and links to the site
 *
 * @param array $opts
 */
function tml_powered_by_tag($opts = array())
{
    echo "<a href='http://translationexchange.com' style='color: #888; font-size: 12px;'>";
    tre("Powered by {company}", array("company" => "Translation Exchange"));
    echo "</a>";
}

/**
 * Displays toggler for help
 *
 * @param null $language
 * @param array $opts
 */
function tml_toggle_inline_mode_tag($opts = array())
{
    $toggle_off = isset($opts['toggle_off']) ? $opts['toggle_off'] : "Help Us Translate";
    $toggle_on = isset($opts['toggle_on']) ? $opts['toggle_on'] : "Deactivate Translation Mode";
    echo "<a href='javascript:void(0);' onclick='Tml.Utils.toggleInlineTranslations()'>";
    if (Session::instance()->isInlineTranslationModeEnabled())
        tre($toggle_on);
    else
        tre($toggle_off);
    echo "</a>";
}

/**
 * @param null $language
 */
function tml_language_attribute($language = null)
{
    if ($language == null) $language = tml_current_language();
    echo "lang='" . $language->locale . "'";
}

/**
 * @param null $language
 */
function tml_language_dir_attribute($language = null)
{
    if ($language == null) $language = tml_current_language();
    echo "dir='" . $language->direction() . "'";
}

/**
 * @param null $language
 */
function tml_language_dir_attr($language = null)
{
    tml_language_dir_attribute($language);
}

/**
 * @param null $language
 */
function tml_html_attributes($language = null)
{
    if ($language == null) $language = tml_current_language();
    echo "xml:lang='$language->locale' " . tml_language_dir_attribute($language) . " " . tml_language_attribute($language);
}

/**
 * Displays language name
 *
 * @param \Tml\Language $language
 */
function tml_language_flag_tag($language = null, $opts = array())
{
    if ($language == null) $language = tml_current_language();
    $name = $language->english_name;
    if (isset($opts['language']) && $opts['language'] == 'native')
        $name = $language->native_name;
    echo "<img src='" . $language->flagUrl() . "'  alt='" . $name . "' title='" . $name . "' style='margin-right:3px;'>";
}

/**
 * @param $language
 * @param array $opts
 */
function tml_language_selector_footer_tag($opts = array())
{
    include dirname(__FILE__) . "/" . "FooterScripts.php";
}

/**
 * Language selector
 */
function tml_language_selector_tag($style, $opts = array())
{
    $options = array();
    foreach ($opts as $key => $value) {
        array_push($options, "data-tml-" . $key . "='" . $value . "'");
    }
    $options = implode(" ", $options);
    echo "<div data-tml-language-selector='$style' $options></div>";
}


/**
 * Stylesheet alternator for RTL and LTR languages
 *
 * @param $ltr
 * @param $rtl
 * @param array $opts
 */
function tml_stylesheet_link_tag($ltr, $rtl, $opts = array())
{
    $language = tml_current_language();

    if ($language->right_to_left)
        echo '<link rel="stylesheet" href="' . $rtl . '">';
    else
        echo '<link rel="stylesheet" href="' . $ltr . '">';
}

/**
 * Generates a url with locale based on the strategy
 *
 * @param $url
 * @param array $opts
 * @return string
 */
function tml_url_for($url, $opts = array())
{
    if (preg_match('#^https?://#i', $url) !== 1) {
        if (Session::localeStrategy() == 'pre-path') {
            $fragments = StringUtils::split($url);
            array_unshift($fragments, tml_current_locale());
            $url = UrlUtils::urlFor(null, StringUtils::join($fragments, '/'));
        }
    }

   return $url;
}

/**
 * Creates a link with a locale based on the strategy
 *
 * @param $label
 * @param string $url
 * @param array $opts
 */
function tml_link_to($label, $url = '#', $opts = array())
{
    if (preg_match('#^https?://#i', $url) !== 1) {
        if (Session::localeStrategy() == 'pre-path') {
            $fragments = StringUtils::split($url);
            array_unshift($fragments, tml_current_locale());
            $url = UrlUtils::urlFor(null, StringUtils::join($fragments, '/'));
        }
    }

    $html = '<a href="' . tml_url_for($url, $opts) . '"';
    foreach ($opts as $key => $value) {
        $html = $html . ' ' . $key . '="' . str_replace('"', '\"', $value) . '"';
    }
    $html = $html . '>' . $label . '</a>';

    echo $html;
}
