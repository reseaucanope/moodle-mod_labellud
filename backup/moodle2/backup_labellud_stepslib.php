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
 * @package mod_labellud
 * @copyright 2022 Reseau-Canope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_label_activity_task
 */

/**
 * Define the complete labellud structure for backup, with file and id annotations
 */
class backup_labellud_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // Define each element separated
        $labellud = new backup_nested_element('labellud', array('id'), array(
            'name', 'intro', 'introformat', 'timemodified', 'type', 'renamed'));

        // Define sources
        $labellud->set_source_table('labellud', array('id' => backup::VAR_ACTIVITYID));

        // Define file annotations
        $labellud->annotate_files('mod_labellud', 'intro', null); // This file area hasn't itemid

        // Return the root element (label), wrapped into standard activity structure
        return $this->prepare_activity_structure($labellud);
    }
}
