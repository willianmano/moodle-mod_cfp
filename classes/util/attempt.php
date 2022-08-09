<?php
// This file is part of BBCalendar block for Moodle - http://moodle.org/
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
 * CFP Utility class
 *
 * @package    mod_cfp
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_cfp\util;

defined('MOODLE_INTERNAL') || die();

/**
 * Utility Assign Tutor class.
 *
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt {
    const NAO_APROVADO = 1;
    const APROVADO_APOS_MODIFICACOES = 2;
    const APROVADO_SEM_MODIFICACOES = 3;

    public function save($context, $data) {
        global $USER, $DB;

        $transaction = $DB->start_delegated_transaction();

        try {
            $questionutil = new \mod_cfp\util\question();
            $fields = $questionutil->get_activity_questions($data->cfpid);

            $attempt = new \stdClass();
            $attempt->cfpid = $data->cfpid;
            $attempt->userid = $USER->id;
            $attempt->step = 1;
            $attempt->status = self::NAO_APROVADO;
            $attempt->timecreated = time();

            $attemptid = $DB->insert_record('cfp_attempts', $attempt);

            $answers = clone $data;
            $answers = (array) $answers;
            foreach ($fields as $field) {
                $answer = new \stdClass();
                $answer->fieldid = $field->id;
                $answer->attemptid = $attemptid;
                $answer->answer = $answers[$field->name];
                $answer->timecreated = time();

                $DB->insert_record('cfp_answers', $answer);
            }

            $options = [
                'subdirs' => 0,
                'maxfiles' => 1,
                'accepted_types' => ['document', 'presentation']
            ];

            // Process attachments.
            $draftitemid = file_get_submitted_draft_itemid('attachment_nonidentified');
            file_save_draft_area_files($draftitemid, $context->id, 'mod_cfp', 'attachment_nonidentified', $attemptid, $options);

            $draftitemid = file_get_submitted_draft_itemid('attachment_identified');
            file_save_draft_area_files($draftitemid, $context->id, 'mod_cfp', 'attachment_identified', $attemptid, $options);

            $DB->commit_delegated_transaction($transaction);

            // Process event.
            $params = array(
                'context' => $context,
                'objectid' => $data->cfpid,
                'relateduserid' => $USER->id
            );
            $event = \mod_cfp\event\attempt_sent::create($params);
            $event->trigger();
        } catch (\Exception $e) {
            $DB->rollback_delegated_transaction($transaction, $e);
        }
    }

    public function update($context, $course, $cm, $cfp, $data) {
        global $USER, $DB;

        $transaction = $DB->start_delegated_transaction();

        try {
            $userutil = new \mod_cfp\util\user($cfp->id);
            $questionutil = new \mod_cfp\util\question();
            $fields = $questionutil->get_activity_questions($data->cfpid);

            $userutil->delete_submission();

            $attempt = new \stdClass();
            $attempt->cfpid = $data->cfpid;
            $attempt->userid = $USER->id;
            $attempt->step = 1;
            $attempt->status = self::NAO_APROVADO;
            $attempt->timecreated = time();

            $attemptid = $DB->insert_record('cfp_attempts', $attempt);

            $answers = clone $data;
            $answers = (array) $answers;
            foreach ($fields as $field) {
                $answer = new \stdClass();
                $answer->fieldid = $field->id;
                $answer->attemptid = $attemptid;
                $answer->answer = $answers[$field->name];
                $answer->timecreated = time();

                $DB->insert_record('cfp_answers', $answer);
            }

            $options = [
                'subdirs' => 0,
                'maxfiles' => 1,
                'accepted_types' => ['document', 'presentation']
            ];

            // Process attachments.
            $draftitemid = file_get_submitted_draft_itemid('attachment_nonidentified');
            file_save_draft_area_files($draftitemid, $context->id, 'mod_cfp', 'attachment_nonidentified', $attemptid, $options);

            $draftitemid = file_get_submitted_draft_itemid('attachment_identified');
            file_save_draft_area_files($draftitemid, $context->id, 'mod_cfp', 'attachment_identified', $attemptid, $options);

            $DB->commit_delegated_transaction($transaction);

            // Process event.
//            $params = array(
//                'context' => $context,
//                'objectid' => $data->cfpid,
//                'relateduserid' => $USER->id
//            );
//            $event = \mod_assigntutor\event\submission_updated::create($params);
//            $event->trigger();

        } catch (\Exception $e) {
            $DB->rollback_delegated_transaction($transaction, $e);
        }
    }

    public function get_status_string($status = 0) {
        if ($status == self::APROVADO_APOS_MODIFICACOES) {
            return get_string('status_aprovadoaposmodificacoes', 'mod_cfp');
        }

        if ($status == self::APROVADO_SEM_MODIFICACOES) {
            return get_string('status_aprovadosemmodificacoes', 'mod_cfp');
        }

        return get_string('status_naoaprovado', 'mod_cfp');
    }

    public function get_status_alert($status = 0) {
        if ($status == self::APROVADO_APOS_MODIFICACOES) {
            return '<div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">Envio aprovado após modificações.</h4>
                        <p>Parabéns, seu envio foi aprovado!</p>
                    </div>';
        }

        if ($status == self::APROVADO_SEM_MODIFICACOES) {
            return '<div class="alert alert-success" role="alert">
                        <h4 class="alert-heading">Envio aprovado sem modificações.</h4>
                        <p>Parabéns, seu envio foi aprovado!</p>
                    </div>';
        }

        return '<div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">Envio não aprovado.</h4>
                        <p>Seu envio não foi avaliado ainda ou não foi aprovado!</p>
                    </div>';
    }

    public function get_attachment($context, $attemptid, $filearea) {
        $fs = get_file_storage();

        $files = $fs->get_area_files($context->id,
            'mod_cfp',
            $filearea,
            $attemptid,
            'timemodified',
            false);

        if (!$files) {
            return false;
        }

        foreach ($files as $file) {
            $path = [
                '',
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $attemptid . $file->get_filepath() . $file->get_filename()
            ];

            $fileurl = \moodle_url::make_file_url('/pluginfile.php', implode('/', $path), true);

            return [
                    'filename' => $file->get_filename(),
                    'isimage' => $file->is_valid_image(),
                    'fileurl' => $fileurl->out()
            ];
        }

        return false;
    }
}
