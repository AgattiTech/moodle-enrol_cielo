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
 * cielo enrolments plugin settings and presets.
 *
 * @package    enrol_cielo
 * @copyright  2020 Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('enrol_cielo_settings',
        '', get_string('pluginname_desc', 'enrol_cielo')));

    $settings->add(new admin_setting_configcheckbox('enrol_cielo/usesandbox',
        get_string('usesandbox', 'enrol_cielo'), get_string('usesandboxdesc', 'enrol_cielo'), 0));

    $settings->add(new admin_setting_configtext('enrol_cielo/merchantid',
        get_string('merchantid', 'enrol_cielo'), get_string('merchantid_desc', 'enrol_cielo'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('enrol_cielo/merchantkey',
        get_string('merchantkey', 'enrol_cielo'), get_string('merchantkey_desc', 'enrol_cielo'), '', PARAM_RAW));

    $settings->add(new admin_setting_configcheckbox('enrol_cielo/mailstudents',
        get_string('mailstudents', 'enrol_cielo'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_cielo/mailteachers',
        get_string('mailteachers', 'enrol_cielo'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_cielo/mailadmins',
        get_string('mailadmins', 'enrol_cielo'), '', 0));

    $settings->add(new admin_setting_configcheckbox(
        'enrol_cielo/mailfromsupport',
        get_string('mailfromsupport', 'enrol_cielo'),
        get_string('mailfromsupport_desc', 'enrol_cielo'),
        0));

    $settings->add(new admin_setting_configcheckbox('enrol_cielo/automaticenrolboleto',
        get_string('automaticenrolboleto', 'enrol_cielo'),
        get_string('automaticenrolboleto_desc', 'enrol_cielo'),
        0));

    $settings->add(new admin_setting_configcheckbox(
        'enrol_cielo/transparentcheckout',
        get_string('transparentcheckout', 'enrol_cielo'),
        '',
        0));

    $settings->add(new admin_setting_heading('enrol_cielo_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_cielo/status',
        get_string('status', 'enrol_cielo'), get_string('status_desc', 'enrol_cielo'), ENROL_INSTANCE_DISABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_cielo/cost',
        get_string('cost', 'enrol_cielo'), '', 0, PARAM_FLOAT, 4));

    $settings->add(new admin_setting_configtext('enrol_cielo/currency',
        get_string('currency', 'enrol_cielo'), get_string('currency_desc', 'enrol_cielo'), 'BRL', PARAM_RAW));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_cielo/roleid',
            get_string('defaultrole', 'enrol_cielo'), get_string('defaultrole_desc', 'enrol_cielo'),
            $student->id, $options));
    }

    $settings->add(new admin_setting_configtext('enrol_cielo/enrolperiod',
        get_string('enrolperiod', 'enrol_cielo'), get_string('enrolperiod_desc', 'enrol_cielo'), 0, PARAM_INT));
}
