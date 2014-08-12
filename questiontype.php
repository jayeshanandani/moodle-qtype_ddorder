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
 * The question type class for the ddorder question type.
 *
 * @package   qtype_ddorder
 * @copyright 2014 Jayesh Anandani
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');

class qtype_ddorder extends question_type {

	public function get_question_options($question) {
        global $DB;
        parent::get_question_options($question);
        $question->options = $DB->get_record('qtype_ddorder_options', array('questionid' => $question->id));
        $question->options->subquestions = $DB->get_records('question_answers',
                array('question' => $question->id), 'id ASC');
        return true;
    }

    public function save_question_options($question) {
        global $DB;
        $context = $question->context;
        $result = new stdClass();

        $oldsubquestions = $DB->get_records('question_answers',
                array('question' => $question->id), 'id ASC');

        // $subquestions will be an array with subquestion ids
        $subquestions = array();

        $ordercount = 1;
        foreach ($question->subquestions as $key => $questiontext) {
            if ($questiontext['text'] == '') {
                continue;
            }

            // Update an existing subquestion if possible.
            $subquestion = array_shift($oldsubquestions);
            if (!$subquestion) {
                $subquestion = new stdClass();
                while ($DB->record_exists('question_answers',
                        array('question' => $question->id))) {
                }
                $subquestion->question = $question->id;
                $subquestion->answer = '';
                $subquestion->feedback = '';
                $subquestion->id = $DB->insert_record('question_answers', $subquestion);
            }

            $subquestion->answer = $this->import_or_save_files($questiontext,
                    $context, 'qtype_ddorder_options', 'subquestion', $subquestion->id);
            $subquestion->question = $question->id;
            $DB->update_record('question_answers', $subquestion);

            $subquestions[] = $subquestion->id;
        }

        // Delete old subquestions records
        $fs = get_file_storage();
        foreach ($oldsubquestions as $oldsub) {
            $fs->delete_area_files($context->id, 'qtype_ddorder_options', 'subquestion', $oldsub->id);
            $DB->delete_records('question_answers', array('id' => $oldsub->id));
        }

        // Save the question options.
        $options = $DB->get_record('qtype_ddorder_options', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->id = $DB->insert_record('qtype_ddorder_options', $options);
        }

        //$options->subquestions = implode(',', $subquestions);
        $options->horizontal = $question->horizontal;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('qtype_ddorder_options', $options);

        $this->save_hints($question, true);

        if (!empty($result->notice)) {
            return $result;
        }

        if (count($subquestions) < 3) {
            $result->notice = get_string('notenoughanswers', 'question', 3);
            return $result;
        }

        return true;
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
    }

    protected function make_hint($hint) {
        return question_hint_with_parts::load_from_record($hint);
    }

    public function delete_question($questionid, $contextid) {
        global $DB;
        $DB->delete_records('qtype_ddorder_options', array('questionid' => $questionid));
        $DB->delete_records('question_answers', array('question' => $questionid));

        parent::delete_question($questionid, $contextid);
    }

    public function get_random_guess_score($questiondata) {
        $q = $this->make_question($questiondata);
        return 1 / count($q->choices);
    }

    /**
     * @return array of the grading styles possible.
     */
    public static function get_grading_styles() {
        $styles = array();
        foreach (array('linearmapping', 'allornone') as $gradingoption) {
            $styles[$gradingoption] =
                    get_string('grading' . $gradingoption, 'qtype_ddorder');
        }
        return $styles;
    }

    public function get_possible_responses($questiondata) {
        $subqs = array();

        $q = $this->make_question($questiondata);

        foreach ($q->stems as $stemid => $stem) {

            $responses = array();
            foreach ($q->choices as $choiceid => $choice) {
                $responses[$choiceid] = new question_possible_response(
                        $q->html_to_text($stem, $q->stemformat[$stemid]) . ': ' . $choice,
                        ($choiceid == $q->right[$stemid]) / count($q->stems));
            }
            $responses[null] = question_possible_response::no_response();

            $subqs[$stemid] = $responses;
        }

        return $subqs;
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

   protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_combined_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'ddorder') {
            return false;
        }
        $question = parent::import_from_xml($data, $question, $format, null);
        $format->import_combined_feedback($question, $data, true);
        $format->import_hints($question, $data, true, false, $format->get_format($question->questiontextformat));
        return $question;
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $output = parent::export_to_xml($question, $format);
        $output .= $format->write_combined_feedback($question->options, $question->id, $question->contextid);
        return $output;
    }
}