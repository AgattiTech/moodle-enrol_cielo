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

require('../../config.php');
require_once('edit_form.php');

$courseid   = required_param('courseid', PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('enrol/cielo:config', $context);

$PAGE->set_url('/enrol/cielo/edit.php', array('courseid' => $course->id, 'id' => $instanceid));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id' => $course->id));
if (!enrol_is_enabled('cielo')) {
    redirect($return);
}

$plugin = enrol_get_plugin('cielo');

if ($instanceid) {
    $instance = $DB->get_record('enrol',
        array('courseid' => $course->id, 'enrol' => 'cielo', 'id' => $instanceid),
        '*', MUST_EXIST);
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // No instance yet, we have to add new instance.
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id' => $course->id)));
    $instance = new stdClass();
    $instance->id       = null;
    $instance->courseid = $course->id;
}

$mform = new enrol_cielo_edit_form(null, array($instance, $plugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) {

    if ($instance->id) {
        $reset = ($instance->status != $data->status);

        $instance->status         = $data->status;
        $instance->name           = $data->name;
        $instance->cost           = $data->cost;
        $instance->customint1     = $data->customint2 ? null : $data->customint1;
        $instance->customint2     = $data->customint2;
        $instance->customtext1    = $data->customint2 ? $data->customtext1 : null;
        $instance->currency       = $data->currency;
        $instance->roleid         = $data->roleid;
        $instance->enrolperiod    = $data->customint2 ? null : $data->enrolperiod;
        $instance->enrolstartdate = $data->customint2 ? null : $data->enrolstartdate;
        $instance->enrolenddate   = $data->enrolenddate;
        $instance->timemodified   = time();
        $DB->update_record('enrol', $instance);

        if ($reset) {
            $context->mark_dirty();
        }

    } else {
        $fields = array(
            'status'         => $data->status,
            'name'           => $data->name,
            'cost'           => $data->cost,
            'customint1'     => $data->customint2 ? null : $data->customint1,
            'customint2'     => $data->customint2,
            'customtext1'    => $data->customint2 ? $data->customtext1 : null,
            'currency'       => $data->currency,
            'roleid'         => $data->roleid,
            'enrolperiod'    => $data->customint2 ? null : $data->enrolperiod,
            'enrolstartdate' => $data->customint2 ? null : $data->enrolstartdate,
            'enrolenddate'   => $data->enrolenddate
        );
        $plugin->add_instance($course, $fields);
    }

    redirect($return);
}

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_cielo'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_cielo'));
$mform->display();
echo $OUTPUT->footer();
