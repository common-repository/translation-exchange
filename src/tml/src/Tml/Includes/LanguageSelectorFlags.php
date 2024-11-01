<?php include dirname(__FILE__)."/"."LanguageSelectorJs.php" ?>

<?php

$style = isset($opts['style']) ? $opts['style'] : '';
$class = isset($opts['class']) ? $opts['class'] : '';
$opts['flag'] = true;

echo "<div id='tml_language_selector' style='$style' class='$class'>";

$languages = \Tml\Config::instance()->application->languages;
foreach($languages as $lang) {
    echo "<a href='#' onclick=\"tml_change_locale('" . $lang->locale . "')\">";
    tml_language_flag_tag($lang, $opts);
    echo "</a> ";
}
echo "</div>";
