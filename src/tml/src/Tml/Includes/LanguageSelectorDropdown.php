<?php include dirname(__FILE__)."/"."LanguageSelectorJs.php" ?>

<?php

$style = isset($opts['style']) ? $opts['style'] : '';
$class = isset($opts['class']) ? $opts['class'] : '';
$type = isset($opts['type']) ? $opts['type'] : 'english';

echo "<select id='tml_language_selector' onchange='tml_change_locale(this.options[this.selectedIndex].value)' style='$style' class='$class'>";

$languages = \Tml\Config::instance()->application->languages;
foreach($languages as $lang) {
    echo "<option dir='ltr' value='$lang->locale' " . ($lang->locale == tml_current_language()->locale ? 'selected' : '') . ">";
    if ($type == "native")
        echo $lang->native_name;
    else
        echo $lang->english_name;
    echo "</option>";
}
echo "</select>";

tml_language_selector_footer_tag($opts);

