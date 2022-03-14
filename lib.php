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
 * Library of functions and constants for module labellud
 *
 * @package mod_labellud
 * @copyright  2022 Reseau-Canope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/labellud/locallib.php');

/** labellud_MAX_NAME_LENGTH = 50 */
define("labellud_MAX_NAME_LENGTH", 50);

/**
 * @uses labellud_MAX_NAME_LENGTH
 * @param object $labellud
 * @return string
 */
function get_labellud_name($labellud) {
    $name = strip_tags(format_string($labellud->intro,true));
    if (core_text::strlen($name) > labellud_MAX_NAME_LENGTH) {
        $name = core_text::substr($name, 0, labellud_MAX_NAME_LENGTH)."...";
    }

    if (empty($name)) {
        // arbitrary name
        $name = get_string('modulename','labellud');
    }

    return $name;
}
/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $labellud
 * @return bool|int
 */
function labellud_add_instance($label) {
    global $DB;

    if (isset($label->enable_custom_name) && $label->enable_custom_name && isset($label->custom_name) && !empty(trim($label->custom_name))) {
        $label->renamed = 1;
        $label->name = $label->custom_name;
    } else {
        $label->name = labellud::get_type($label->type)->name;
    }
    
    $label->timemodified = time();
    $id = $DB->insert_record('labellud', $label);

    $completiontimeexpected = !empty($label->completionexpected) ? $label->completionexpected : null;
    \core_completion\api::update_completion_date_event($label->coursemodule, 'labellud', $id, $completiontimeexpected);

    return $id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $labellud
 * @return bool
 */
function labellud_update_instance($label) {
    global $DB;

    if (isset($label->enable_custom_name) && $label->enable_custom_name && isset($label->custom_name) && !empty(trim($label->custom_name))) {
        $label->renamed = 1;
        $label->name = trim($label->custom_name);
    } else {
        $label->name = labellud::get_type($label->type)->name;
    }
    $label->type = $label->type;
    
    $label->timemodified = time();
    $label->id = $label->instance;

    $completiontimeexpected = !empty($label->completionexpected) ? $label->completionexpected : null;
    \core_completion\api::update_completion_date_event($label->coursemodule, 'labellud', $label->id, $completiontimeexpected);

    return $DB->update_record('labellud', $label);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function labellud_delete_instance($id) {
    global $DB;

    if (! $labellud = $DB->get_record('labellud', array('id'=>$id))) {
        return false;
    }

    $result = true;

    $cm = get_coursemodule_from_instance('labellud', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'labellud', $labellud->id, null);

    if (! $DB->delete_records('labellud', array('id'=>$labellud->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return cached_cm_info|null
 */
function labellud_get_coursemodule_info($coursemodule) {
    global $DB;

    if ($label = $DB->get_record('labellud', array('id'=>$coursemodule->instance), 'id, name, intro, introformat, type, renamed')) {
        if (empty($label->name)) {
            // labellud name missing, fix it
            $label->name = 'labellud'.$label->id;
            $DB->set_field('labellud', 'name', $label->name, array('id'=>$label->id));
        }
        $info = new cached_cm_info();
        // no filtering hre because this info is cached and filtered later
        $info->content = format_module_intro('labellud', $label, $coursemodule->id, false);
        $info->name  = $label->name;
        $info->customdata = array('type' => $label->type);
        return $info;
    } else {
        return null;
    }
}


function labellud_cm_info_view(cm_info $cm){
    global $PAGE, $DB;
    
    $label = $DB->get_record('labellud', array('id'=>$cm->instance), 'id, name, intro, introformat, type, renamed');
    
    $label_type = labellud::get_type($label->type);
    
    if (empty($label->renamed)){
        $label->name = $label_type->name;
    }
    
    $label->color = $label_type->color;
    $label->fontcolor = $label_type->fontcolor;
    $label->fontawesomeicon = $label_type->fontawesomeicon;
    
    $renderer = $PAGE->get_renderer('mod_labellud');
    $cm->set_content($renderer->display($label, $cm));
}


function labellud_cm_info_dynamic(cm_info $cm){
    $context = context_course::instance($cm->course);
    
    if (!has_capability('mod/labellud:view_all_label', $context)) {
        $type = labellud::get_type($cm->customdata['type']);
        if (count($type->roles) > 0) {
            $user_roles = get_user_roles($context);
            
            $canview = false;
            foreach ($user_roles AS $user_role) {
                if (in_array($user_role->id,$type->roles) ) {
                    $canview = true;
                }
            }
            
            if (!$canview) {
                $cm->set_available(false, 0);
                $cm->set_user_visible(false);
            }
        }
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function labellud_reset_userdata($data) {

    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.

    return array();
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function labellud_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return true;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_NO_VIEW_LINK:            return true;

        default: return null;
    }
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function labellud_dndupload_register() {
    $strdnd = get_string('dnduploadlabellud', 'mod_labellud');
    if (get_config('labellud', 'dndmedia')) {
        $mediaextensions = file_get_typegroup('extension', ['web_image', 'web_video', 'web_audio']);
        $files = array();
        foreach ($mediaextensions as $extn) {
            $extn = trim($extn, '.');
            $files[] = array('extension' => $extn, 'message' => $strdnd);
        }
        $ret = array('files' => $files);
    } else {
        $ret = array();
    }

    $strdndtext = get_string('dnduploadlabelludtext', 'mod_labellud');
    return array_merge($ret, array('types' => array(
        array('identifier' => 'text/html', 'message' => $strdndtext, 'noname' => true),
        array('identifier' => 'text', 'message' => $strdndtext, 'noname' => true)
    )));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function labellud_dndupload_handle($uploadinfo) {
    global $USER;

    // Gather the required info.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '';
    $data->introformat = FORMAT_HTML;
    $data->coursemodule = $uploadinfo->coursemodule;

    // Extract the first (and only) file from the file area and add it to the labellud as an img tag.
    if (!empty($uploadinfo->draftitemid)) {
        $fs = get_file_storage();
        $draftcontext = context_user::instance($USER->id);
        $context = context_module::instance($uploadinfo->coursemodule);
        $files = $fs->get_area_files($draftcontext->id, 'user', 'draft', $uploadinfo->draftitemid, '', false);
        if ($file = reset($files)) {
            if (file_mimetype_in_typegroup($file->get_mimetype(), 'web_image')) {
                // It is an image - resize it, if too big, then insert the img tag.
                $config = get_config('labellud');
                $data->intro = labellud_generate_resized_image($file, $config->dndresizewidth, $config->dndresizeheight);
            } else {
                // We aren't supposed to be supporting non-image types here, but fallback to adding a link, just in case.
                $url = moodle_url::make_draftfile_url($file->get_itemid(), $file->get_filepath(), $file->get_filename());
                $data->intro = html_writer::link($url, $file->get_filename());
            }
            $data->intro = file_save_draft_area_files($uploadinfo->draftitemid, $context->id, 'mod_labellud', 'intro', 0,
                                                      null, $data->intro);
        }
    } else if (!empty($uploadinfo->content)) {
        $data->intro = $uploadinfo->content;
        if ($uploadinfo->type != 'text/html') {
            $data->introformat = FORMAT_PLAIN;
        }
    }

    return labellud_add_instance($data, null);
}

/**
 * Resize the image, if required, then generate an img tag and, if required, a link to the full-size image
 * @param stored_file $file the image file to process
 * @param int $maxwidth the maximum width allowed for the image
 * @param int $maxheight the maximum height allowed for the image
 * @return string HTML fragment to add to the labellud
 */
function labellud_generate_resized_image(stored_file $file, $maxwidth, $maxheight) {
    global $CFG;

    $fullurl = moodle_url::make_draftfile_url($file->get_itemid(), $file->get_filepath(), $file->get_filename());
    $link = null;
    $attrib = array('alt' => $file->get_filename(), 'src' => $fullurl);

    if ($imginfo = $file->get_imageinfo()) {
        // Work out the new width / height, bounded by maxwidth / maxheight
        $width = $imginfo['width'];
        $height = $imginfo['height'];
        if (!empty($maxwidth) && $width > $maxwidth) {
            $height *= (float)$maxwidth / $width;
            $width = $maxwidth;
        }
        if (!empty($maxheight) && $height > $maxheight) {
            $width *= (float)$maxheight / $height;
            $height = $maxheight;
        }

        $attrib['width'] = $width;
        $attrib['height'] = $height;

        // If the size has changed and the image is of a suitable mime type, generate a smaller version
        if ($width != $imginfo['width']) {
            $mimetype = $file->get_mimetype();
            if ($mimetype === 'image/gif' || $mimetype === 'image/jpeg' || $mimetype === 'image/png') {
                require_once($CFG->libdir.'/gdlib.php');
                $data = $file->generate_image_thumbnail($width, $height);

                if (!empty($data)) {
                    $fs = get_file_storage();
                    $record = array(
                        'contextid' => $file->get_contextid(),
                        'component' => $file->get_component(),
                        'filearea'  => $file->get_filearea(),
                        'itemid'    => $file->get_itemid(),
                        'filepath'  => '/',
                        'filename'  => 's_'.$file->get_filename(),
                    );
                    $smallfile = $fs->create_file_from_string($record, $data);

                    // Replace the image 'src' with the resized file and link to the original
                    $attrib['src'] = moodle_url::make_draftfile_url($smallfile->get_itemid(), $smallfile->get_filepath(),
                                                                    $smallfile->get_filename());
                    $link = $fullurl;
                }
            }
        }

    } else {
        // Assume this is an image type that get_imageinfo cannot handle (e.g. SVG)
        $attrib['width'] = $maxwidth;
    }

    $img = html_writer::empty_tag('img', $attrib);
    if ($link) {
        return html_writer::link($link, $img);
    } else {
        return $img;
    }
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.2
 */
function labellud_check_updates_since(cm_info $cm, $from, $filter = array()) {
    $updates = course_check_module_updates_since($cm, $from, array(), $filter);
    return $updates;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid User id to use for all capability checks, etc. Set to 0 for current user (default).
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_labellud_core_calendar_provide_event_action(calendar_event $event,
                                                      \core_calendar\action_factory $factory,
                                                      int $userid = 0) {
    $cm = get_fast_modinfo($event->courseid, $userid)->instances['labellud'][$event->instance];

    if (!$cm->uservisible) {
        // The module is not visible to the user for any reason.
        return null;
    }

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_url('/mod/labellud/view.php', ['id' => $cm->id]),
        1,
        true
    );
}
