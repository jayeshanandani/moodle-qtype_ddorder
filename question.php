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
 * ddorder question definition class.
 *
 * @package   qtype_ddorder
 * @copyright 2014 Jayesh Anandani
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class qtype_ddorder_question extends question_graded_automatically_with_countback {
	public $answer;
    public $shuffledanswers;
    public $correctfeedback;
    public $partiallycorrectfeedback = '';
    public $incorrectfeedback = '';
    public $correctfeedbackformat;
    public $partiallycorrectfeedbackformat;
    public $incorrectfeedbackformat;
    public $fraction;

    /** @var array of question_answer. */
    public $answers = array();

    /**
     * @var array place number => group number of the places in the question
     * text where choices can be put. Places are numbered from 1.
     */
    public $places = array();

    /**
     * @var array of strings, one longer than $places, which is achieved by
     * indexing from 0. The bits of question text that go between the placeholders.
     */
    public $textfragments;

    /** @var array index of the right choice for each stem. */
    public $rightchoices;
    public $allanswers = array();

    public function get_expected_data() {
        return array('answers' => PARAM_SEQUENCE);
    }

    public function summarise_response(array $response) {
       
    }

    public function is_complete_response(array $response) {
      
    }

    public function get_validation_error(array $response) {
           
    }

    public function get_right_choice_for($place) {
       
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        
    }

    public function get_correct_response() {
       
    }

    /* called from within renderer in interactive mode */

    public function is_correct_response($answergiven, $rightanswer) {
       
    }

    /**
     *
     * @param array $response Passed in from the submitted form
     * @return array 
     *
     * Find count of correct answers, used for displaying marks
     * for question. Compares answergiven with right/correct answer
     */
    public function get_num_parts_right(array $response) {
     
    }

    /**
     * Given a response, rest the parts that are wrong. Relevent in 
     * interactive with multiple tries
     * @param array $response a response
     * @return array a cleaned up response with the wrong bits reset.
     */
    public function clear_wrong_from_response(array $response) {
   
    }


    public function grade_response(array $response) {
    }

    // Required by the interface question_automatically_gradable_with_countback.
    public function compute_final_grade($responses, $totaltries) {

    }


    public function compare_response_with_answer($answergiven, $answer, $casesensitive, $disableregex = false) {

    }

   
}
