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

namespace Tml\Tokenizers;

use Tml\Config;
use Tml\Session;
use Tml\Utils\StringUtils;

class DomTokenizer {
    /**
     * @var string
     */
    public $text;

    /**
     * Original context
     * @var mixed[]
     */
    public $context;

    /**
     * Dynamic tokens built at parse time
     * @var mixed[]
     */
    public $tokens;

    /**
     * Used for testing and debugging tokens
     * @var mixed[]
     */
    public $debug_tokens;

    /**
     * @var mixed[]
     */
    public $options;

    /**
     * @var \DOMDocument
     */
    public $doc;

    /**
     * @param string $html
     * @param array $context
     * @param array $options
     */
    function __construct($html="", $context = array(), $options = array()) {
        $this->html = $html;
        $this->context = $context;
        $this->tokens = array_merge(array(), $this->context);
        $this->options = $options;
        $this->tml = null;
        $this->parseDocument();
    }

    /**
     * Prepares HTML for processing
     */
    function prepareHtml() {
        // remove all tabs and new lines - as they mean nothing in HTML
        $this->html = trim(preg_replace('/\t\n/', '', $this->html));

        // normalize multiple spaces to one space
        $this->html = preg_replace('/\s+/', ' ', $this->html);

//        $charset = 'UTF-8';
//        if (function_exists('mb_convert_encoding') && in_array(strtolower($charset), array_map('strtolower', mb_list_encodings()))) {
//            $this->html = mb_convert_encoding($this->html, 'HTML-ENTITIES', $charset);
//        }

    }

    /**
     * Parses the HTML document
     */
    function parseDocument() {
        $this->prepareHtml();

        $current = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        $charset = 'UTF-8';
        $this->doc = new \DOMDocument('1.0', $charset);
        $this->doc->strictErrorChecking = false;

        @$this->doc->loadHTML($this->html);

        libxml_use_internal_errors($current);
        libxml_disable_entity_loader($disableEntities);
    }

    /**
     * @param string $html
     * @return array
     */
    public function translate($html = null) {
        if ($html!=null) {
            $this->html = $html;
            $this->parseDocument();
        }
        return $this->translateTree($this->doc);
    }

    /**
     * @param $node
     * @return string
     */
    private function sanitizeNodeValue($node) {
        $value = $node->wholeText;
        $value = str_replace("\n", "", $value);
        $value = trim($value);
        return $value;
    }

    private function hasChildNodes($node) {
        if (!isset($node->childNodes)) return false;
        return (array_count_values($node->childNodes) > 0);
    }


    private function isBetweenSeparators($node) {
        if ($this->isSeparatorNode($node->previousSibling) && !$this->isValidTextNode($node->nextSibling))
            return true;
        if ($this->isSeparatorNode($node->nextSibling) && !$this->isValidTextNode($node->previousSibling))
            return true;
        return false;
    }

    private function innerHtml($element) {
        $innerHTML = "";
        $children  = $element->childNodes;

        foreach ($children as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }

    /**
     * Return source name for a given element
     *
     * @param $node
     * @return null
     */
    private function getSourceBlock($node) {
        if (!$node->attributes) return null;
        return $node->getAttribute('data-tml-source');
    }

    /**
     * @param $node
     * @return mixed|string
     */
    private function translateTree($node) {
        if ($this->isNonTranslatableNode($node)) {
            return $this->innerHtml($node);
        }

        if ($node->nodeType == 3) {
            return $this->translateTml($node->wholeText);
        }

        $source = null;
        if ($node->nodeType == 1) {
            $source = $this->getSourceBlock($node);
            if ($source) Session::beginSource($source);
        }

        $html = "";
        $buffer = "";
        if (isset($node->childNodes)) {
            foreach($node->childNodes as $child) {
                if ($child->nodeType == 3) {                    // text node
                    $buffer = $buffer . $child->wholeText;
                } else if ($this->isInlineNode($child) && $this->hasInlineOrTextSiblings($child) && !$this->isBetweenSeparators($child)) {  // inline nodes - tml
                    $buffer = $buffer . $this->generateTmlTags($child);
                } else if ($this->isSeparatorNode($child)) {    // separators:  br or hr
                    if ($buffer != "")
                        $html = $html . $this->translateTml($buffer);
                    $html = $html . $this->generateHtmlToken($child);
                    $buffer = "";
                } else {                                        // nested container nodes
                    if ($buffer != "")
                        $html = $html . $this->translateTml($buffer);

                    $container_value = $this->translateTree($child);
                    if ($this->isIgnoredNode($child)) {
                        $html = $html . $container_value;
                    } else {
                        $html = $html . $this->generateHtmlToken($child, $container_value);
                    }

                    $buffer = "";
                }
            }
        }

        if ($buffer != "") {
            $html = $html . $this->translateTml($buffer);
        }

        if ($source) Session::finishSource();

        return $html;
    }

    /**
     * @param $node
     * @return bool
     */
    private function isNoTranslate($node) {
        if ($node->attributes) {
            foreach($node->attributes as $attr) {
                if ($attr->name == 'notranslate')
                    return true;
                if ($attr->name == 'class' && strpos($attr->value, 'notranslate') !== false)
                    return true;
            }
        }

        return false;
    }

    /**
     * Checks if the node needs to be translated or ignored
     *
     * @param $node
     * @return bool
     */
    private function isNonTranslatableNode($node) {
        if (!$node) return false;

        if ($node->nodeType == 8)
            return true;

        if ($node->nodeType == 1) {
            if (in_array(strtolower($node->nodeName), $this->getOption("nodes.scripts")))
                return true;

            if (count($node->childNodes) === 0 && $node->nodeValue === '')
                return true;

            if ($this->isNoTranslate($node))
                return true;
        }

        return false;
    }

    /**
     * TML nodes can be nested - but they CANNOT contain non-inline nodes
     *
     * @param $node
     * @return string
     */
    private function generateTmlTags($node) {
        $buffer = "";
        foreach($node->childNodes as $child) {
            if ($child->nodeType == 3) {                    // text node
                $buffer = $buffer . $child->wholeText;
            } else {
                $buffer = $buffer . $this->generateTmlTags($child);
            }
        }

        $token_context = $this->generateHtmlToken($node);
        $token = $this->adjustName($node);
        $token = $this->contextualize($token, $token_context);

        $value = $this->sanitizeValue($buffer);

        if ($this->isSelfClosingNode($node))
            return '{'.$token.'}';

        if ($this->isShortToken($token, $value))
            return '['.$token.': '.$value.']';

        return '<'.$token.'>'.$value.'</'.$token.'>';
    }

    /**
     * @param $name
     * @return mixed|null|string|\string[]
     */
    private function getOption($name) {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }
        return Config::instance()->configValue("translator_options." . $name);
    }

    /**
     * @param $translation
     * @return mixed
     */
    private function debugTranslation($translation) {
        return str_replace('{$0}', $translation, $this->getOption("debug_format"));
    }


    /**
     * @param $tml
     * @return bool
     */
    private function isEmptyString($tml) {
        $tml = trim($tml," \n\r\t\0\x0b\xa0\xc2");
        return ($tml == '');
    }

    /**
     * Resets context of the current translation
     */
    private function resetContext() {
        $this->debug_tokens = $this->tokens;
        $this->tokens = array_merge(array(), $this->context);
    }

    /**
     * @param $tml
     * @return string
     */
    private function translateTml($tml) {
        if ($this->isEmptyString($tml)) return $tml;

//        \Tml\Logger::instance()->info("Translating: ##" . $tml . "##", $this->tokens);

        $tml = $this->generateDataTokens($tml);

        $language = Session::instance()->current_language;
        if ($language == null) $language = Config::instance()->defaultLanguage();

        if ($this->getOption("split_sentences")) {
            $sentences = StringUtils::splitSentences($tml);

//            \Tml\Logger::instance()->info(json_encode($sentences));

            $translation = $tml;
            foreach($sentences as $sentence) {
                $sentence_translation = $this->getOption("debug") ? $this->debugTranslation($sentence) : $language->translate($sentence, null, $this->tokens, $this->options);
                $translation = str_replace($sentence, $sentence_translation, $translation);
            }
            $this->resetContext();
            return $translation;
        }

        $translation =  $this->getOption("debug") ? $this->debugTranslation($tml) : $language->translate($tml, null, $this->tokens, $this->options);
        $this->resetContext();
        return $translation;
    }

    /**
     * @param $node
     * @return bool
     */
    private function isOnlyChild($node) {
        if (!isset($node->parentNode)) return false;
        return ($node->parentNode->childNodes->length == 1);
    }

    /**
     * @param $node
     * @return bool
     */
    private function hasInlineOrTextSiblings($node) {
        if (!isset($node->parentNode)) return false;

        foreach($node->parentNode->childNodes as $child) {
            if ($child === $node) continue;
            if ($this->isInlineNode($child) || $this->isValidTextNode($child))
                return true;
        }

        return false;
    }

    /**
     * @param $node
     * @return bool
     */
    private function isInlineNode($node) {
        return ($node->nodeType == 1
            && in_array($node->tagName, $this->getOption("nodes.inline"))
            && !$this->isOnlyChild($node)
        );
    }

    /**
     * @param $node
     * @return bool
     */
    private function isContainerNode($node) {
        return ($node->nodeType == 1 && !$this->isInlineNode($node));
    }

    /**
     * @param $node
     * @return bool
     */
    private function isSelfClosingNode($node) {
        return ($node->firstChild == null);
    }

    /**
     * @param $node
     * @return bool
     */
    private function isIgnoredNode($node) {
        if ($node->nodeType != 1) return true;
        return in_array($node->tagName, $this->getOption("nodes.ignored"));
    }

    /**
     * @param $node
     * @return bool
     */
    private function isValidTextNode($node) {
        if ($node == null) return false;
        return ($node->nodeType == 3 && !$this->isEmptyString($node->wholeText));
    }

    /**
     * @param $node
     * @return bool
     */
    private function isSeparatorNode($node) {
        if ($node == null) return false;
        return ($node->nodeType == 1 && in_array($node->tagName, $this->getOption("nodes.splitters")));
    }

    /**
     * @param string $value
     * @return string
     */
    private function sanitizeValue($value) {
        $value = ltrim($value);
        return $value;
    }

    /**
     * @param string $text
     * @return mixed
     */
    private function generateDataTokens($text) {
        if ($this->getOption("data_tokens.special.enabled")) {
            preg_match_all($this->getOption("data_tokens.special.regex"), $text, $matches);
            $matches = array_unique($matches[0]);

            foreach ($matches as $match) {
                $token = substr($match, 1, -1);
                $this->context[$token] = $match;
                $text = str_replace($match, "{" . $token . "}", $text);
            }
        }

        if ($this->getOption("data_tokens.date.enabled")) {
            $token_name = $this->getOption('data_tokens.date.name');
            $formats = $this->getOption('data_tokens.date.formats');
            foreach($formats as $format) {
                $regex = $format[0];
                # date_format = format[1]

                $matches = array();
                preg_match_all($regex, $text, $matches);
                $matches = array_unique($matches[0]);

                foreach ($matches as $match) {
                    $date = trim($match, ' ');
                    if ($date == "") continue;
                    $token = $this->contextualize($token_name, $date);
                    $text = str_replace($date, "{" . $token . "}", $text);
                }
            }
        }

        $rules = $this->getOption("data_tokens.rules");
        if ($rules) {
            foreach ($rules as $rule) {
                if (!$rule["enabled"]) continue;
                $matches = array();
                preg_match_all($rule["regex"], $text, $matches);
                $matches = array_unique($matches[0]);

//                var_dump($rule["regex"]);
//                var_dump($matches);

                foreach ($matches as $match) {
                    $value = trim($match, ' ');
                    if ($value == "") continue;
                    $token = $this->contextualize($rule["name"], str_replace("/[.,;\\s]/", "", $value));
                    $text = str_replace($match, str_replace($value, "{" . $token . "}", $match), $text);
                }
            }
        }

        return $text;
    }

    /**
     * @param $node
     * @param null $value
     * @return string
     */
    private function generateHtmlToken($node, $value = null) {
        $name = $node->tagName;
        $attributes = $node->attributes;
        $attributes_array = array();
        $value = $value == null ? '{$0}' : $value;

        foreach($attributes as $attr) {
            $attributes_array[$attr->name] = $attr->value;
        }

        if (count($attributes_array) == 0) {
            if ($this->isSelfClosingNode($node))
                return '<'.$name.'/>';
            return '<'.$name.'>' . $value . '</'.$name.'>';
        }

        $keys = array_keys($attributes_array);
        arsort($keys);

        $attr = array();
        foreach($keys as $key) {
            $quote = (strpos($attributes_array[$key], "'") !== FALSE ? '"' : "'");
            array_push($attr, $key.'='.$quote.$attributes_array[$key].$quote);
        }
        $attr = implode(' ', $attr);

        if ($this->isSelfClosingNode($node))
            return '<'.$name.' '.$attr.'/>';

        return '<'.$name.' '.$attr.'>' . $value . '</'.$name.'>';
    }

    /**
     * @param $node
     * @return mixed
     */
    private function adjustName($node) {
        $name = $node->tagName;
        $map = $this->getOption("name_mapping");
        $name = isset($map[$name]) ? $map[$name] : $name;
        return $name;
    }

    /**
     * @param string $name
     * @param string $context
     * @return string
     */
    private function contextualize($name, $context) {
        if (isset($this->tokens[$name])) {
            if ($this->tokens[$name] != $context) {
                $index = 0;
                if (preg_match_all("/.*?(\d+)$/", $name, $matches)>0) {
                    $index = $matches[count($matches)-1][0];
                    $name = str_replace($index, '', $name);
                }
                $name = $name . ($index + 1);
                return $this->contextualize($name, $context);
            }
        }

        $this->tokens[$name] = $context;
        return $name;
    }

    /**
     * @param string $token
     * @param string $value
     * @return bool
     */
    private function isShortToken($token, $value) {
        if (in_array($token, $this->getOption("nodes.short")))
            return true;

        if (strlen($value) < 20)
            return true;

        return false;
    }

    /**
     * @param null $html
     */
    public function debug($html = null) {
        if ($html!=null)
            $this->html = $html;

        $this->parseDocument();
        print_r("\n\n");
        $this->debugTree($this->doc);
        print_r("\n\n");
    }

    /**
     * @param $node
     * @return string
     */
    private function nodeInfo($node) {
        $info = array();
        array_push($info, $node->nodeType);

        if ($node->nodeType == 1)
            array_push($info, $node->tagName);

        if ($this->isInlineNode($node)) {
            array_push($info, "inline");
            if ($this->hasInlineOrTextSiblings($node))
                array_push($info, "sentence");
            else
                array_push($info, "only translatable");
        }

        if ($this->isSelfClosingNode($node))
            array_push($info, "self closing");

        if ($this->isOnlyChild($node))
            array_push($info, "only child");

        if ($node->nodeType == 3)
            return "[" . implode(", ", $info) . "]" . ': "'.$node->wholeText.'"';

        return "[" . implode(", ", $info) . "]";
    }


    /**
     * @param $node
     * @param int $depth
     */
    private function debugTree($node, $depth = 0) {
        $padding = str_repeat('=', $depth);

        print_r($padding . "=> " . get_class($node) . ": " . $this->nodeInfo($node) . "\n");
//        print_r($node);

        if (isset($node->childNodes)) {
            foreach($node->childNodes as $child) {
                $this->debugTree($child, $depth+1);
            }
        }
    }
}