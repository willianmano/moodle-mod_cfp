<?php

namespace mod_cfp\external;

use context;
use external_api;
use external_value;
use external_single_structure;
use external_function_parameters;

class question extends external_api {
    /**
     * Add question parameters
     *
     * @return external_function_parameters
     */
    public static function add_parameters() {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'jsonformdata' => new external_value(PARAM_RAW, 'The data from the question form, encoded as a json array')
        ]);
    }

    /**
     * Add question method
     *
     * @param int $contextid
     * @param string $jsonformdata
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function add($contextid, $jsonformdata) {
        global $DB;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::add_parameters(),
            ['contextid' => $contextid, 'jsonformdata' => $jsonformdata]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $serialiseddata = json_decode($params['jsonformdata']);

        $data = [];
        parse_str($serialiseddata, $data);

        $mform = new \mod_cfp\forms\question($data, ['cmid' => $context->instanceid]);

        $validateddata = $mform->get_data();

        if (!$validateddata) {
            throw new \moodle_exception('invalidformdata');
        }

        list($course, $cm) = get_course_and_cm_from_cmid($context->instanceid);

        $moduleinstance = $DB->get_record($cm->modname, ['id' => $cm->instance]);

        $data = new \stdClass();
        $data->cfpid = $moduleinstance->id;
        $data->type = $validateddata->type;
        $data->name = $validateddata->name;
        $data->label = $validateddata->label;
        $data->options = $validateddata->options;
        $data->timecreated = time();

        $questionutil = new \mod_cfp\util\question();
        $question = $questionutil->create_question($data);

        return [
            'status' => 'ok',
            'message' => get_string('addquestion_success', 'mod_cfp'),
            'data' => json_encode($question)
        ];
    }

    /**
     * Add question return fields
     *
     * @return external_single_structure
     */
    public static function add_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_RAW, 'Return message'),
                'data' => new external_value(PARAM_RAW, 'Return data')
            )
        );
    }

    /**
     * Delete question parameters
     *
     * @return external_function_parameters
     */
    public static function delete_parameters() {
        return new external_function_parameters([
            'question' => new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The question id', VALUE_REQUIRED)
            ])
        ]);
    }

    /**
     * Delete question method
     *
     * @param array $question
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function delete($question) {
        global $DB;

        self::validate_parameters(self::delete_parameters(), ['question' => $question]);

        $question = (object)$question;

        $DB->delete_records('cfp_fields', ['id' => $question->id]);

        return [
            'status' => 'ok',
            'message' => get_string('deletequestion_success', 'mod_cfp')
        ];
    }

    /**
     * Delete gradeitem return fields
     *
     * @return external_single_structure
     */
    public static function delete_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Operation status'),
                'message' => new external_value(PARAM_TEXT, 'Return message')
            )
        );
    }
}
