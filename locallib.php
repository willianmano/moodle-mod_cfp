<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

function mod_cfp_add_submission($context, $course, $cm, $data) {
    global $USER, $DB;

    $transaction = $DB->start_delegated_transaction();

    try {
        $questionutil = new \mod_cfp\util\question();
        $questions = $questionutil->get_activity_questions($data->cfpid);

        $answers = clone $data;
        $answers = (array) $answers;
        foreach ($questions as $question) {
            $answer = new \stdClass();
            $answer->fieldid = $question->id;
            $answer->userid = $USER->id;
            $answer->answer = $answers[$question->name];
            $answer->timecreated = time();

            $DB->insert_record('cfp_answers', $answer);
        }

        $DB->commit_delegated_transaction($transaction);

        // Process event.
        $params = array(
            'context' => $context,
            'objectid' => $data->cfpid,
            'relateduserid' => $USER->id
        );
        $event = \mod_assigntutor\event\submission_sent::create($params);
        $event->trigger();

        // Completion progress
        $completion = new completion_info($course);
        $completion->update_state($cm, COMPLETION_COMPLETE);

    } catch (Exception $e) {
        $DB->rollback_delegated_transaction($transaction, $e);
    }
}

function mod_cfp_update_submission($context, $course, $cm, $cfp, $data) {
    global $USER, $DB;

    $transaction = $DB->start_delegated_transaction();

    try {
        $userutil = new \mod_cfp\util\user($cfp);
        $questionutil = new \mod_cfp\util\question();
        $questions = $questionutil->get_activity_questions($data->cfpid);

        $userutil->delete_submission();

        $answers = clone $data;
        $answers = (array) $answers;
        foreach ($questions as $question) {
            $answer = new \stdClass();
            $answer->fieldid = $question->id;
            $answer->userid = $USER->id;
            $answer->answer = $answers[$question->name];
            $answer->timecreated = time();

            $DB->insert_record('cfp_answers', $answer);
        }

        $DB->commit_delegated_transaction($transaction);

        // Process event.
        $params = array(
            'context' => $context,
            'objectid' => $data->cfpid,
            'relateduserid' => $USER->id
        );
        $event = \mod_assigntutor\event\submission_updated::create($params);
        $event->trigger();

        // Completion progress
        $completion = new completion_info($course);
        $completion->update_state($cm, COMPLETION_COMPLETE);

    } catch (Exception $e) {
        $DB->rollback_delegated_transaction($transaction, $e);
    }
}