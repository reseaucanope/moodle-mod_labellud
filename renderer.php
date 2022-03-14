<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Labellud module
 *
 * @package mod_labellud
 * @copyright  2022 TCS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_labellud_renderer extends plugin_renderer_base {
    public function display($label, $cm){
        $data = array(
            'title' => $label->name,
            'fontawesomeicon' => $label->fontawesomeicon,
            'fontcolor' => $label->fontcolor,
            'backgroundcolor' => $label->color,
            'content' => format_module_intro('labellud', $label, $cm->id, false)
        );
        
        return parent::render_from_template('mod_labellud/labelcontent', $data);
    }
}