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
 * This file keeps track of upgrades to the cielo enrolment plugin
 *
 * @package    enrol_cielo
 * @subpackage cielo
 * @copyright  2010 Eugene Venter
 * @author     Eugene Venter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

defined('MOODLE_INTERNAL') || die;
/**
 * This function will attempt to upgrade from older versions
 *
 * @param mixed $oldversion
 * @return boolean
 */
function xmldb_enrol_cielo_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2021070503) {

        // Define field recurrentpaymentid to be added to enrol_cielo.
        $table = new xmldb_table('enrol_cielo');
        $field = new xmldb_field('recurrentpaymentid', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'tid');

        // Conditionally launch add field recurrentpaymentid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Cielo savepoint reached.
        upgrade_plugin_savepoint(true, 2021070503, 'enrol', 'cielo');
    }
    return true;
}
