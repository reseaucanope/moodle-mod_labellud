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
 * Add labellud form
 *
 * @package mod_labellud
 * @copyright  2022 Reseau-Canope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once ($CFG->dirroot.'/mod/labellud/locallib.php');

class mod_labellud_mod_form extends moodleform_mod {

    function definition() {
        global $PAGE;

        $PAGE->force_settings_menu();

        $mform = $this->_form;
        $data = $this->current;
        
        $types = labellud::get_types();
        $formtypes = array();
        foreach ($types as $type) {
            $formtypes[$type->id] = $type->name;
        }
        
        $mform->addElement('header', 'typehdr', get_string('label_display_options', labellud::PLUGINNAME));
        $type_select = $mform->addElement('select', 'type', get_string('label_type', labellud::PLUGINNAME), $formtypes);
        
        $enable_custom_name_checkbox = $mform->addElement('advcheckbox', 'enable_custom_name', get_string('enable_custom_name', labellud::PLUGINNAME), null, 'class="fitem_fcheckbox"');
        $name_text = $mform->addElement('text', 'custom_name', get_string('custom_name', labellud::PLUGINNAME), 'maxlength="80" size="80"');
        $mform->setType('custom_name', PARAM_TEXT);
        
        
        $mform->addFormRule(array($this,'validate_name'));
        
        if(!empty($data->type)){
            $type_select->setSelected($data->type);
        }
        if (!empty($data->renamed)) {
            $enable_custom_name_checkbox->setValue(true);
            $name_text->setValue($data->name);
        }
        
        $mform->disabledIf('custom_name', 'enable_custom_name');

        $mform->addElement('header', 'generalhdr', get_string('general'));
        $mform->setExpanded('generalhdr');
        $this->standard_intro_elements(get_string('labeltext', labellud::PLUGINNAME));

        $mform->addElement('hidden', 'showdescription', 1);
        $mform->setType('showdescription', PARAM_INT);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons(true, false, null);
    }
    
    function validate_name($fields) {
        if (isset($fields['enable_custom_name']) && $fields['enable_custom_name'] && (!isset($fields['custom_name']) || trim(strlen($fields['custom_name'])) < 3)) {
            return array('custom_name' => get_string('custom_name_not_valid', labellud::PLUGINNAME));
        }
        return true;
    }

}
