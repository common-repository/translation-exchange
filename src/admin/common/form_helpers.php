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

function help_tag($text)
{
    ?>

    <div class="help" style="display: inline-block; margin-left: 10px;">
        ?
        <div class="tooltip">
            <?php echo $text ?>
        </div>
    </div>

    <?php
}

function text_area_tag($name, $value, $options = array())
{
    ?>

    <textarea
        name="<?php echo $name ?>"
        placeholder="<?php echo isset($options['placeholder']) ? $options['placeholder'] : '' ?>"
        style="background-color: #f8f8f8;<?php echo isset($options['style']) ? $options['style'] : '' ?>"
        ><?php echo $value ?></textarea>

    <?php
}

function text_field_tag($name, $value, $options = array())
{
    ?>

    <input
        type="text"
        name="<?php echo $name ?>"
        value="<?php echo $value ?>"
        placeholder="<?php echo isset($options['placeholder']) ? $options['placeholder'] : '' ?>"
        style="background-color: #f8f8f8;<?php echo isset($options['style']) ? $options['style'] : '' ?>"
        >

    <?php
}

function radio_button_tag($name, $value, $options = array())
{

    $disabled = isset($options['disabled']) && $options['disabled'];
    $checked = isset($options['checked']) && $options['checked'];
    ?>

    <?php if (isset($options['label'])) { ?>
        <label title="<?php echo $options['label'] ?>">
    <?php } ?>

    <input
        type="radio"
        name="<?php echo $name ?>"
        value="<?php echo $value ?>"
        <?php echo ($checked & !$disabled) ? 'checked' : '' ?>
        <?php echo $disabled ? 'disabled' : '' ?>
        >

    <?php if (isset($options['label'])) { ?>
        <?php echo $options['label'] ?></label>
    <?php } ?>

    <?php
}

function check_box_tag($name, $value = "true", $options = array())
{
    ?>

    <input
        type="checkbox"
        name="<?php echo $name ?>"
        value="<?php echo $value ?>"
        <?php echo isset($options['checked']) && $options['checked'] ? 'checked' : '' ?>
        <?php echo isset($options['disabled']) && $options['disabled'] ? 'disabled' : '' ?>
        >

    <?php
}

function span_tag($text, $style = "")
{
    ?>

    <span style="<?php echo $style ?>"><?php echo $text ?></span>
    <?php
}