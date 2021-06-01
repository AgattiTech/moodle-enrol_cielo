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
 * Cielo enrolment plugin.
 *
 * This plugin allows you to set up paid courses.
 *
 * @package    enrol_cielo
 * @copyright  2020 Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * cielo enrolment plugin implementation.
 * @author  Eugene Venter - based on code by Martin Dougiamas and others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_cielo_plugin extends enrol_plugin {

    /**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * We are not using single instance parameter because sometimes
     * we might want to prevent icon repetition when multiple instances
     * of one type exist. One instance may also produce several icons.
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $instances) {
        return array(new pix_icon('cielo', get_string('pluginname', 'enrol_cielo'), 'enrol_cielo'));
    }

    /**
     * Checks if there are any protected roles.
     *
     * @return boolean
     */
    public function roles_protected() {
        return false;
    }

    /**
     * Checks if there are any protected roles.
     *
     * @param stdClass $instance
     * @return boolean
     */
    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    /**
     * Checks if management is allowed.
     *
     * @param stdClass $instance
     * @return boolean
     */
    public function allow_manage(stdClass $instance) {
        return true;
    }

    /**
     * Shows the self enrolment link.
     *
     * @param stdClass $instance
     * @return boolean
     */
    public function show_enrolme_link(stdClass $instance) {
        return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    /**
     * Sets up navigation entries.
     *
     * @param mixed $instancesnode
     * @param stdClass $instance
     * @return void
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'cielo') {
             throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/cielo:config', $context)) {
            $urlparams = ['courseid' => $instance->courseid, 'id' => $instance->id];
            $managelink = new moodle_url('/enrol/cielo/edit.php', $urlparams);
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'cielo') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/cielo:config', $context)) {
            $editlinkparams = ['courseid' => $instance->courseid, 'id' => $instance->id];
            $editlink = new moodle_url("/enrol/cielo/edit.php", $editlinkparams);
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core', ['class' => 'icon']));
        }

        return $icons;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = context_course::instance($courseid);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/cielo:config', $context)) {
            return null;
        }

        return new moodle_url('/enrol/cielo/edit.php', array('courseid' => $courseid));
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $CFG, $USER, $OUTPUT, $PAGE, $DB;

        ob_start();

        if ($DB->record_exists('user_enrolments', array('userid' => $USER->id, 'enrolid' => $instance->id))) {
            return ob_get_clean();
        }

        if ($instance->enrolstartdate != 0 && $instance->enrolstartdate > time()) {
            return ob_get_clean();
        }

        if ($instance->enrolenddate != 0 && $instance->enrolenddate < time()) {
            return ob_get_clean();
        }

        $course = $DB->get_record('course', array('id' => $instance->courseid));
        $context = context_course::instance($instance->courseid);

        $shortname = format_string($course->shortname, true, array('context' => $context));
        $strloginto = get_string("loginto", "", $shortname);
        $strcourses = get_string("courses");

        // Pass $view=true to filter hidden caps if the user cannot see them.
        if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
                                             '', '', '', '', false, true)) {
            $users = sort_by_roleassignment_authority($users, $context);
            $teacher = array_shift($users);
        } else {
            $teacher = false;
        }

        if ( (float) $instance->cost <= 0 ) {
            $cost = (float) $this->get_config('cost');
        } else {
            $cost = (float) $instance->cost;
        }

        if (abs($cost) < 0.01) { // No cost, other enrolment methods (instances) should be used.
            echo '<p>' . get_string('nocost', 'enrol_cielo') . '</p>';
        } else {

            if (isguestuser()) { // Force login only for guest user, not real users with guest role.
                echo '<div class="mdl-align">',
                     '<p>', get_string('paymentrequired'), '</p>',
                     '<p><b>', get_string('cost'), ': ', $instance->currency, ' ', $cost, '</b></p>',
                     '<p>', get_string('needsignuporlogin', 'enrol_cielo'), '</p>',
                     '<p><a href="', new moodle_url('/login'), '">', get_string('loginsite'), '</a></p>',
                     '</div>';
            } elseif ($instance->customint2) {
                $installments = array();
                for($i = 1; $i <= $instance->customint1; $i++){
                    $n = array();
                    $n['value'] = $i;
                    $n['text'] = $i."x";
                    $installments[] = $n;
                }

                $tcdata = array();
                $tcdata["requestpayment"] = get_string('paymentrequiredp1', 'enrol_cielo', $instance);
                $tcdata["requestpaymentcost"] = $instance->cost;
                $tcdata["requestpaymentp2"] = get_string('paymentrequiredp2', 'enrol_cielo');
                $tcdata["instanceName"] = $this->get_instance_name($instance);
                $tcdata["instanceid"] = $instance->id;
                $tcdata["courseid"] = $instance->courseid;
                $tcdata["buttonString"] = get_string('sendpaymentbuttonrecurrent', 'enrol_cielo');
                $tcdata["cfgRoot"] = $CFG->wwwroot;
                $tcdata["courseP"] = (float) $instance->cost;
                $tcdata["getSessionUrl"] = new moodle_url('/enrol/cielo/tr_process.php');
                $tcdata["installments"] = $installments;
                $tcdata["fullname"] = "$USER->firstname $USER->lastname";
                if ($USER->cpf) {
                    $tcdata["cpf"] = $USER->cpf;
                }
                if ($USER->logradouro) {
                    $tcdata["logradouro"] = $USER->logradouro;
                }
                if ($USER->cep) {
                    $tcdata["cep"] = $USER->cep;
                }
                if ($USER->numero) {
                    $tcdata["numero"] = $USER->numero;
                }
                if ($USER->bairro) {
                    $tcdata["bairro"] = $USER->bairro;
                }
                if ($USER->cidade) {
                    $tcdata["cidade"] = $USER->cidade;
                }
                if ($USER->uf) {
                    $tcdata["uf"] = $USER->uf;
                }
                if ($USER->complemento) {
                    $tcdata["complemento"] = $USER->complemento;
                }
                $output = $OUTPUT->render_from_template("enrol_cielo/recurrentcheckout", $tcdata);
                
                return $output;
            } else {
                $installments = array();
                for($i = 1; $i <= $instance->customint1; $i++){
                    $n = array();
                    $n['value'] = $i;
                    $n['text'] = $i."x";
                    $installments[] = $n;
                }

                $tcdata = array();
                $tcdata["requestpayment"] = get_string('paymentrequiredp1', 'enrol_cielo', $instance);
                $tcdata["requestpaymentcost"] = $instance->cost;
                $tcdata["requestpaymentp2"] = get_string('paymentrequiredp2', 'enrol_cielo');
                $tcdata["instanceName"] = $this->get_instance_name($instance);
                $tcdata["instanceid"] = $instance->id;
                $tcdata["courseid"] = $instance->courseid;
                $tcdata["buttonString"] = get_string('sendpaymentbutton', 'enrol_cielo');
                $tcdata["cfgRoot"] = $CFG->wwwroot;
                $tcdata["courseP"] = (float) $instance->cost;
                $tcdata["getSessionUrl"] = new moodle_url('/enrol/cielo/tr_process.php');
                $tcdata["installments"] = $installments;
                $tcdata["fullname"] = "$USER->firstname $USER->lastname";
                if ($USER->cpf) {
                    $tcdata["cpf"] = $USER->cpf;
                }
                if ($USER->logradouro) {
                    $tcdata["logradouro"] = $USER->logradouro;
                }
                if ($USER->cep) {
                    $tcdata["cep"] = $USER->cep;
                }
                if ($USER->numero) {
                    $tcdata["numero"] = $USER->numero;
                }
                if ($USER->bairro) {
                    $tcdata["bairro"] = $USER->bairro;
                }
                if ($USER->cidade) {
                    $tcdata["cidade"] = $USER->cidade;
                }
                if ($USER->uf) {
                    $tcdata["uf"] = $USER->uf;
                }
                if ($USER->complemento) {
                    $tcdata["complemento"] = $USER->complemento;
                }
                $output = $OUTPUT->render_from_template("enrol_cielo/transparentcheckout", $tcdata);

                return $output;

            }

        }

        return $OUTPUT->box(ob_get_clean());
    }

    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/self:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/cielo:config', $context);
    }

}
