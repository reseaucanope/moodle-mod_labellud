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
 * PHPUnit labellud generator tests
 *
 * @package    mod_labellud
 * @category   phpunit
 * @copyright  2022 Reseau-Canope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * PHPUnit labellud generator testcase
 *
 * @package    mod_labellud
 * @category   phpunit
 * @copyright  2022 Reseau-Canope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_labellud_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('labellud'));

        $course = $this->getDataGenerator()->create_course();

        /** @var mod_labellud_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_labellud');
        $this->assertInstanceOf('mod_labellud_generator', $generator);
        $this->assertEquals('labellud', $generator->get_modulename());

        $generator->create_instance(array('course'=>$course->id));
        $generator->create_instance(array('course'=>$course->id));
        $label = $generator->create_instance(array('course'=>$course->id));
        $this->assertEquals(3, $DB->count_records('labellud'));

        $cm = get_coursemodule_from_instance('labellud', $label->id);
        $this->assertEquals($label->id, $cm->instance);
        $this->assertEquals('labellud', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($label->cmid, $context->instanceid);
    }
}
