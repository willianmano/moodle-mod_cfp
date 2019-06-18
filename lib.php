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
 * @package    mod_cfp
 * @copyright  2019 Willian Mano {@link http://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function cfp_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_ASSIGNMENT;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
    }
}

/**
 * Saves a new instance of the cfp into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $cfp Submitted data from the form in mod_form.php
 * @param mod_cfp_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted cfp record
 */
function cfp_add_instance(stdClass $cfp, mod_cfp_mod_form $mform = null) {
    global $DB;

    $query = "SELECT *
              FROM {cfp} np
              INNER JOIN {course_modules} cm ON np.id = cm.instance
              INNER JOIN {modules} mo ON mo.id = cm.module
              WHERE np.course = :courseid
              AND deletioninprogress <> 1
              AND mo.name = 'cfp'";
    
    $parameters = ['courseid' => $cfp->course];

    // Get user logs.
    $ispresentoncourse = $DB->get_records_sql($query, $parameters);

    if ($ispresentoncourse) {
        \core\notification::add('Você só pode adicionar uma instância do módulo <b>Call for papers</b> por curso.', \core\notification::ERROR);

        redirect(new moodle_url('/course/view.php', ['id' => $cfp->course]));
    }

    $cfp->timecreated = time();
    $cfp->timemodified = time();

    // You may have to add extra stuff in here.
    $cfp->id = $DB->insert_record('cfp', $cfp);

    return $cfp->id;
}

/**
 * Updates an instance of the cfp in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $cfp An object from the form in mod_form.php
 * @param mod_cfp_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function cfp_update_instance(stdClass $cfp, mod_cfp_mod_form $mform = null) {
    global $DB;

    $cfp->timemodified = time();
    $cfp->id = $cfp->instance;

    // You may have to add extra stuff in here.
    $result = $DB->update_record('cfp', $cfp);

    return $result;
}

/**
 * Removes an instance of the cfp from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function cfp_delete_instance($id) {
    global $DB;

    if (!$cfp = $DB->get_record('cfp', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.
    $DB->delete_records('cfp', array('id' => $cfp->id));

    return true;
}

function cfp_add_submission($data) {
    global $DB, $USER;

    unset($data->submitbutton);

    $data->userid = $USER->id;
    $data->status = 'analise';
    $data->timecreated = time();
    $data->timemodified = time();

    return $DB->insert_record('cfp_submissions', $data);
}