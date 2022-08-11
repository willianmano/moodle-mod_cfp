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

/**
 * This function extends the settings navigation block for the site.
 *
 * It is safe to rely on PAGE here as we will only ever be within the module
 * context when this is called
 *
 * @param settings_navigation $settings
 * @param navigation_node $modnode
 * @return void
 */
function cfp_extend_settings_navigation($settings, $modnode) {
    global $PAGE;

    if (!has_capability('mod/cfp:addinstance', $PAGE->cm->context)) {
        return false;
    }

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $modnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    $node = navigation_node::create(get_string('editquestions', 'mod_cfp'),
        new moodle_url('/mod/cfp/questions.php', array('id' => $PAGE->cm->id)),
        navigation_node::TYPE_SETTING, null, 'mod_cfp_editquestions',
        new pix_icon('t/edit', ''));
    $modnode->add_node($node, $beforekey);
}

/**
 * Fragment used in add question modal
 *
 * @param array $args
 *
 * @return string
 */
function mod_cfp_output_fragment_question_form($args) {
    $args = (object) $args;
    $context = $args->context;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    $mform = new \mod_cfp\forms\question($formdata, ['cmid' => $context->instanceid]);

    if (!empty($args->jsonformdata)) {
        // If we were passed non-empty form data we want the mform to call validation functions and show errors.
        $mform->is_validated();
    }

    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();

    return $o;
}

/**
 * Serves the files from the mod_cfp file areas.
 *
 * @package     mod_cfp
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_cfp's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function cfp_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, false, $cm);

    $itemid = (int)array_shift($args);
    if ($itemid == 0) {
        return false;
    }

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/mod_cfp/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}
