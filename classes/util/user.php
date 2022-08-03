<?php

namespace mod_cfp\util;

class user {
    protected $cfp;

    public function __construct($cfp) {
        $this->cfp = $cfp;
    }

    public function get_submission($userid = null) {
        global $USER, $DB;

        if (!$userid) {
            $userid = $USER->id;
        }

        $sql = 'SELECT a.*, fi.name, fi.label
                FROM {cfp_answers} a
                INNER JOIN {cfp_fields} fi ON fi.id = a.fieldid
                INNER JOIN {cfp} f ON f.id = fi.formid
                WHERE f.id = :formid AND a.userid = :userid';

        $submission = $DB->get_records_sql($sql, ['formid' => $this->cfp->id, 'userid' => $userid]);

        if (!$submission) {
            return false;
        }

        return $submission;
    }

    public function delete_submission($userid = null) {
        global $USER, $DB;

        if (!$userid) {
            $userid = $USER->id;
        }

        $sql = 'DELETE a
                FROM {cfp_answers} a
                INNER JOIN {cfp_fields} fi ON fi.id = a.fieldid
                INNER JOIN {cfp} f ON f.id = fi.formid
                WHERE f.id = :formid AND a.userid = :userid';

        return $DB->execute($sql, ['formid' => $this->cfp->id, 'userid' => $userid]);
    }

    public function activity_submitted($userid = null) {
        return false;
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $hassubmission = $this->get_submission($userid);

        if ($hassubmission) {
            return true;
        }

        return false;
    }

    public function activity_evaluated($userid = null) {
        return false;
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        return $this->evaluationmethod->activity_has_evaluation($userid);
    }
}
