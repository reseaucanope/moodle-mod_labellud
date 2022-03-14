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
 * @package    mod_labellud
 * @copyright  2022 Reseau-Canope
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class labellud {

    const PLUGINNAME                = 'labellud';
    const PLUGINFULLNAME            = 'mod_'.self::PLUGINNAME;
    const PREFIX_COLOR              = 'color';
    const PREFIX_FONT_COLOR         = 'fontcolor';
    const PREFIX_ROLE               = 'role';
    const PREFIX_TYPECOUNT          = 'typecount';
    const PREFIX_NAME               = 'name';
    const PREFIX_FONTAWESOME_ICON   = 'fontawesomeicon';
    const DEFAULT_TYPECOUNT         = 1;
    
    static function get_course_roles(){
        $roles = get_roles_for_contextlevels(CONTEXT_COURSE);
        return $GLOBALS['DB']->get_records_list('role', 'id', array_values($roles));
    }
    
    static function count(){
        $count = get_config(self::PLUGINNAME, self::PREFIX_TYPECOUNT );
        return $count === false ? 0 : $count;
    }
    
    static function set_defaultcount() {
        set_config(self::PREFIX_TYPECOUNT, self::DEFAULT_TYPECOUNT, self::PLUGINNAME);
    }
    
    static function get_default_type(){
        $labeltype = new stdClass();
        $labeltype->name = get_string('default_name', self::PLUGINNAME);
        $labeltype->fontawesomeicon = '';
        $labeltype->color = get_string('default_color', self::PLUGINNAME);
        $labeltype->roles = array();
        
        return $labeltype;
    }
    
    /*
     * Get label parameters 
     */
    static function get_type($id){
        
        $labeltype = new stdClass();
        
        $labeltype->id = $id;
        
        $labeltype->name = get_config(self::PLUGINNAME, self::PREFIX_NAME.'_'.$id);
        if ($labeltype->name === false){
            return false;
        }

        $labeltype->fontawesomeicon = get_config(self::PLUGINNAME, self::PREFIX_FONTAWESOME_ICON.'_'.$id);
        if ($labeltype->fontawesomeicon === false){
            return false;
        }
        
        $labeltype->color = get_config(self::PLUGINNAME, self::PREFIX_COLOR.'_'.$id);
        if ($labeltype->color === false){
            return false;
        }

        $labeltype->fontcolor = get_config(self::PLUGINNAME, self::PREFIX_FONT_COLOR.'_'.$id);
        if ($labeltype->fontcolor === false){
            return false;
        }
        
        $labeltype->roles = get_config(self::PLUGINNAME, self::PREFIX_ROLE.'_'.$id);
        if ($labeltype->roles === false){
            $labeltype->roles = array();
        }else{
            $labeltype->roles = explode(',', $labeltype->roles);
        }
        
        return $labeltype;
    }
    
    /*
     *  Get all label parameters 
     */
    static function get_types(){
        
        $labeltypes = array();
        $count  = self::count();
        
        for ($i = 1 ; $i <= $count ; $i++){
            $labeltype = self::get_type($i);
            if ($labeltype === false){
                $labeltype = self::get_default_type();
            }
            $labeltypes[$i] = $labeltype;
        }
        
        return $labeltypes;
    }
    
    static function add_separator($settings){
        
        $settings->add(new admin_page_manageqtypes('name', 'visible', null));
        
    }
    
    static function get_settings_type_name($typeid){
        
        $name = self::PLUGINNAME.'/'.self::PREFIX_NAME.'_' .$typeid;
        $visiblename = get_string('settings_type_name', self::PLUGINNAME).' '.$typeid;
        $description = get_string('settings_type_name_desc', self::PLUGINNAME);
        
        return new admin_setting_configtext($name,
            $visiblename,
            $description,
            self::get_default_type()->name,
            PARAM_RAW
        );
    }

    static function get_settings_type_fontawesomeicon($typeid){

        $name = self::PLUGINNAME.'/'.self::PREFIX_FONTAWESOME_ICON.'_' .$typeid;
        $visiblename = get_string('settings_type_fontawesomeicon', self::PLUGINNAME).' '.$typeid;
        $description = get_string('settings_type_fontawesomeicon_desc', self::PLUGINNAME);

        return new admin_setting_configtext($name,
            $visiblename,
            $description,
            '',
            PARAM_RAW
        );
    }
    
    static function get_settings_type_role($typeid){
        $name = self::PLUGINNAME.'/'.self::PREFIX_ROLE.'_'.$typeid;
        $visiblename = get_string('settings_type_roles', self::PLUGINNAME).' '.$typeid;
        $description = get_string('settings_type_roles_desc', self::PLUGINNAME);
        
        $roles = self::get_course_roles();
        
        $form_roles = array();
        foreach ($roles AS $role) {
            $form_roles[$role->id] = $role->name;
        }
        
        return new admin_setting_configmultiselect($name, 
            $visiblename, 
            $description, 
            array(),
            $form_roles
        );
    }
    
    static function get_settings_type_color($typeid){
        $name = self::PLUGINNAME.'/'. self::PREFIX_COLOR.'_' .$typeid;
        $visiblename = get_string('settings_type_color', self::PLUGINNAME).' '.$typeid;
        $description = get_string('settings_type_color_desc', self::PLUGINNAME);
        
        return new admin_setting_configcolourpicker($name,
            $visiblename,
            $description,
            get_string('default_color', self::PLUGINFULLNAME)
        );
        
    }

    static function get_settings_type_fontcolor($typeid){
        $name = self::PLUGINNAME.'/'. self::PREFIX_FONT_COLOR.'_' .$typeid;
        $visiblename = get_string('settings_type_fontcolor', self::PLUGINNAME).' '.$typeid;
        $description = get_string('settings_type_fontcolor_desc', self::PLUGINNAME);

        return new admin_setting_configcolourpicker($name,
            $visiblename,
            $description,
            get_string('default_fontcolor', self::PLUGINNAME)
        );

    }
    
    static function check_config() {
        $count = self::count();
        
        $count_types = 0;
        for ($i = 0 ; $i < $count ; $i++){
            if (self::get_type($i) !== false){
                $count_types++;
            }
        }
        if ($count != $count_types){
            return false;
        }
        return true;
    }
    
    static function clean(){
        $count = self::count();
        
        for($i=$count+1;$count<$count+50;$i++){
            $label = self::get_type($i);
            if ($label !== false){
                self::remove_type($i);
            }else{
                break;
            }
        }
    }
    
    static function remove_type($id){
        set_config(self::PREFIX_NAME.'_'.$id, null, self::PLUGINNAME);
        set_config(self::PREFIX_FONTAWESOME_ICON.'_'.$id, null, self::PLUGINNAME);
        set_config(self::PREFIX_COLOR.'_'.$id, null, self::PLUGINNAME);
        set_config(self::PREFIX_FONT_COLOR.'_'.$id, null, self::PLUGINNAME);
        set_config(self::PREFIX_ROLE.'_'.$id, null, self::PLUGINNAME);
    }

}
