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
 * Assign Tutor Assessment submit page
 *
 * @package    mod_cfp
 * @copyright  2022 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'cfp');
$cfp = $DB->get_record('cfp', ['id' => $cm->instance], '*', MUST_EXIST);

$urlparams = ['id' => $id];

if ($cfp->duedate < time()) {
    $url = new moodle_url('/mod/cfp/view.php', $urlparams);

    redirect($url, get_string('submit_blockmsg', 'mod_cfp'), null, \core\output\notification::NOTIFY_ERROR);
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/cfp:submit', $context);

$url = new moodle_url('/mod/cfp/submit.php', $urlparams);

$formdata = [
    'cfpid' => $cfp->id
];

$form = new \mod_cfp\forms\submit($url, $formdata);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/cfp/view.php', $urlparams));
} else if ($formdata = $form->get_data()) {
    try {
        $data = clone $formdata;

        unset($data->submitbutton);

        $url = new moodle_url('/mod/cfp/view.php', $urlparams);

        $userutil = new \mod_cfp\util\user($cfp->id);

        $attemptutil = new \mod_cfp\util\attempt();

        if (!$userutil->get_attempt()) {
            $attemptutil->save($context, $data);

            redirect($url, 'Avaliação enviada com sucesso.', null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            $attemptutil->update($context, $course, $cm, $cfp, $data);

            redirect($url, 'Avaliação atualizada com sucesso.', null, \core\output\notification::NOTIFY_SUCCESS);
        }
    } catch (\Exception $e) {
        redirect($url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
} else {
    $PAGE->set_url($url);
    $PAGE->set_title(format_string($course->shortname) . ': ' .format_string($cfp->name));
    $PAGE->set_heading(format_string($course->fullname));

    echo $OUTPUT->header();

    $form->display();

    echo $OUTPUT->footer();
}
