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
 * Resource module admin settings and defaults
 *
 * @package    mod_labellud
 * @copyright  2022 Reseau-Canope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/mod/labellud/locallib.php');



if ($ADMIN->fulltree) {
    
    $settings->add(new admin_setting_configcheckbox(labellud::PLUGINNAME .'/dndmedia',
        get_string('dndmedia', labellud::PLUGINNAME), get_string('configdndmedia', labellud::PLUGINNAME), 1));
    
    $settings->add(new admin_setting_configtext(labellud::PLUGINNAME .'/dndresizewidth',
        get_string('dndresizewidth', labellud::PLUGINNAME), get_string('configdndresizewidth', labellud::PLUGINNAME), 400, PARAM_INT, 6));
    
    $settings->add(new admin_setting_configtext(labellud::PLUGINNAME .'/dndresizeheight',
        get_string('dndresizeheight', labellud::PLUGINNAME),
        get_string('configdndresizeheight', labellud::PLUGINNAME),
        400,
        PARAM_INT, 6));
    
//    $settings->add(new admin_setting_configcheckbox(labellud::PLUGINNAME .'/candisablename',
//        get_string('candisablename', labellud::PLUGINNAME), get_string('configcandisablename', labellud::PLUGINNAME), 400, PARAM_INT, 6));
    
    $settings->add(new admin_setting_heading('typeheading', get_string('settings_type_heading', labellud::PLUGINNAME), ''));
    
    $count = labellud::count();
    if ($count == 0){
        labellud::set_defaultcount();
    }
    
    $settings->add(new admin_setting_configselect(labellud::PLUGINNAME .'/'. labellud::PREFIX_TYPECOUNT,
        get_string('settings_type_count', labellud::PLUGINNAME),
        get_string('settings_type_count_desc', labellud::PLUGINNAME),
        1,
        array_combine(range(1,20),range(1,20))
        ));
    
    
    for ($i = 1 ; $i <= labellud::count() ; $i++){
        $settings->add(new admin_setting_heading('type_'.$i, 'Type '.$i, ''));
        $settings->add(labellud::get_settings_type_name($i));
        $settings->add(labellud::get_settings_type_fontawesomeicon($i));
        $settings->add(labellud::get_settings_type_role($i));
        $settings->add(labellud::get_settings_type_color($i));
        $settings->add(labellud::get_settings_type_fontcolor($i));
    }
    
    labellud::clean();
}
