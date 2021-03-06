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
 * Adds new instance of enrol_cielo to specified course or edits current instance.
 *
 * @package    enrol_cielo
 * @copyright  2020 Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Adds new instance of enrol_cielo to specified course or edits current instance.
 *
 * @copyright  2020 Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_cielo_edit_form extends moodleform {

    /**
     * Creates edit form for single course enrolment settings.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_cielo'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_cielo'), $options);
        $mform->setDefault('status', $plugin->get_config('status'));

        $mform->addElement('text', 'cost', get_string('cost', 'enrol_cielo'), array('size' => 4));
        $mform->setType('cost', PARAM_RAW);
        $mform->setDefault('cost', $plugin->get_config('cost'));
        $mform->addHelpButton('cost', 'cost', 'enrol_cielo');

        $mform->addElement('select', 'currency', get_string('currency', 'enrol_cielo'),
                           \get_string_manager()->get_list_of_currencies());
        $mform->setDefault('currency', $plugin->get_config('currency'));
        
        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('roleid'));
        }
        
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_cielo'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));
        
        $mform->addElement('checkbox', 'customint2', get_string('isrecurrent', 'enrol_cielo'), get_string('checkedyesno', 'enrol_cielo'));
        $mform->setType('customint2', PARAM_INT);
        
        $recurringinterval = array(
            'monthly' => 'Monthly',
            'bimonthly' => 'Bimonthly',
            'quarterly' => 'Quarterly',
            'semiannual' => 'SemiAnnual',
            'annual' => 'Annual'
        );
        
        $mform->addElement('select', 'customtext1', get_string('recurringinterval', 'enrol_cielo'),
                           $recurringinterval);
        $mform->setType('customtext1', PARAM_TEXT);
        $mform->disabledIf('customtext1', 'customint2');
        
        $installmentslist = array();
        
        for($i=1; $i <= 12; $i++){
            $installmentslist[$i] = "$i x";
        }
        
        $mform->addElement('select', 'customint1', get_string('installments', 'enrol_cielo'),
                           $installmentslist);
        $mform->setType('customint1', PARAM_INT);
        $mform->disabledIf('customint1', 'customint2', 'checked');

        $options = ['optional' => true, 'defaultunit' => 86400];
        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_cielo'), $options);
        $mform->setDefault('enrolperiod', $plugin->get_config('enrolperiod'));
        $mform->disabledIf('enrolperiod', 'customint2', 'checked');
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_cielo');

        $options = ['optional' => true];
        $mform->addElement('date_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_cielo'), $options);
        $mform->setDefault('enrolstartdate', 0);
        $mform->disabledIf('enrolstartdate', 'customint2', 'checked');
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_cielo');

        $mform->addElement('date_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_cielo'), $options);
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_cielo');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    /**
     * Validates form against enrolment instance status, enrolment date,
     * and the cost value.
     *
     * @param mixed $data
     * @param mixed $files
     * @return mixed $errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['status'] == ENROL_INSTANCE_ENABLED) {
            if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
                $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_cielo');
            }

            if (!is_numeric($data['cost'])) {
                $errors['cost'] = get_string('costerror', 'enrol_cielo');

            }
        }

        return $errors;
    }
}
