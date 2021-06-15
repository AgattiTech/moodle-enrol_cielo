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
 * Listens for Instant Payment Notification from cielo
 *
 * This script waits for Payment notification from cielo,
 * then double checks that data by sending it back to cielo.
 * If cielo verifies this then it sets up the enrolment for that
 * user.
 *
 * @package    enrol_cielo
 * @copyright  2010 Eugene Venter
 * @copyright  2015 Daniel Neis Araujo <danielneis@gmail.com>
 * @author     Eugene Venter - based on code by others
 * @author     Daniel Neis Araujo based on code by Eugene Venter and others
 * @author     Igor Agatti Lima based on code by Eugene Venter, Daniel Neis Araujo and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// @codingStandardsIgnoreLine
require('../../config.php');
require_once("lib.php");
require_once($CFG->libdir.'/enrollib.php');

define('SUCESSO', 4);
define('SUCESSO2', 6);
define('NAO_AUTORIZADO', 05);
define('CARTAO_EXPIRADO', 57);
define('CARTAO_BLOQUEADO', 78);
define('TIME_OUT', 99);
define('CARTAO_CANCELADO', 77);
define('PROBLEMAS_COM_CARTAO', 70); // Valor devolvido para o comprador.
define('STATUS_SUCCESS', 'success');
define('STATUS_FAILURE', 'failure');
define('STATUS_PENDING', 'pending');

$plugin = enrol_get_plugin('cielo');
$merchantid = $plugin->get_config('merchantid');
$merchantkey = $plugin->get_config('merchantkey');

if (get_config('enrol_cielo', 'usesandbox') == 1) {
    $baseurl = 'https://apisandbox.cieloecommerce.cielo.com.br';
    $queryurl = 'https://apiquerysandbox.cieloecommerce.cielo.com.br';
} else {
    $baseurl = 'https://api.cieloecommerce.cielo.com.br';
    $queryurl = 'https://apiquery.cieloecommerce.cielo.com.br';
}

$key = optional_param('notificationCode', '', PARAM_RAW);
$notificationtype = optional_param('notificationType', '', PARAM_RAW);

$json = file_get_contents('php://input');
$data = json_decode($json)

cielo_notificationrequest($data, $merchantid, $merchantkey, $baseurl);


function cielo_notificationrequest($data, $merchantid, $merchantkey, $baseurl) {

    $myfile = fopen("notifications.txt", "a") or fopen("notifications.txt", "w") or die("Unable to open myfile!");
    $txt = var_export($data, true);
    $txt .= "\n\n\n";
    fwrite($myfile, $txt);
    fclose($myfile);

}

/**
 * Receives transaction data and updates the database.
 *
 * @param stdClass $data
 *
 * @return string $id the record id of the updated record in the database
 */
function cielo_handletransactionresponse($data) {

    global $DB;

    $rec = new stdClass();
    $rec->id = $data->reference->__toString();
    $rec->code = $data->code->__toString();
    $rec->type = $data->type->__toString();
    $rec->status = intval($data->status->__toString());
    $rec->paymentmethod_type = $data->paymentMethod->type->__toString();
    $rec->paymentmethod_code = $data->paymentMethod->code->__toString();
    $rec->grossamount = number_format($data->grossAmount->__toString(), 2);
    $rec->discountedamount = $data->discountAmount->__toString();

    switch($rec->status){
        case COMMERCE_CIELO_STATUS_AWAITING:
        case COMMERCE_CIELO_STATUS_IN_ANALYSIS:
            $rec->payment_status = COMMERCE_PAYMENT_STATUS_PENDING;
            break;
        case COMMERCE_CIELO_STATUS_PAID:
        case COMMERCE_CIELO_STATUS_AVAILABLE:
            $rec->payment_status = COMMERCE_PAYMENT_STATUS_SUCCESS;
            break;
        case COMMERCE_CIELO_STATUS_DISPUTED:
        case COMMERCE_CIELO_STATUS_REFUNDED:
        case COMMERCE_CIELO_STATUS_CANCELED:
        case COMMERCE_CIELO_STATUS_DEBITED:
        case COMMERCE_CIELO_STATUS_WITHHELD:
            $rec->payment_status = COMMERCE_PAYMENT_STATUS_FAILURE;
            break;

    }

    $DB->update_record("enrol_cielo", $rec);

    $record = $DB->get_record("enrol_cielo", ['id' => $rec->id]);
    if ($record->payment_status == COMMERCE_PAYMENT_STATUS_SUCCESS) {
        enrol_cielo_coursepaidevent($record);
    }

    return $record;

}

/**
 * Enrols or unenrols user depending on the database record.
 *
 * @param stdClass $rec the record in the database
 *
 * @return void
 */
function cielo_handleenrolment($rec) {
    global $DB;

    $plugin = enrol_get_plugin('cielo');
    $plugininstance = $DB->get_record('enrol', array('courseid' => $rec->courseid, 'enrol' => 'cielo'));

    if ($plugininstance->enrolperiod) {
        $timestart = time();
        $timeend = $timestart + $plugininstance->enrolperiod;
    } else {
        $timestart = 0;
        $timeend   = 0;
    }

    switch ($rec->payment_status) {
        case COMMERCE_PAYMENT_STATUS_SUCCESS:
            $plugin->enrol_user($plugininstance, $rec->userid, $plugininstance->roleid, $timestart, $timeend);
            break;
        case COMMERCE_PAYMENT_STATUS_FAILURE:
            $plugin->unenrol_user($plugininstance, $rec->userid);
            break;
    }

}

/**
 * Triggers payment received event.
 * 
 * @param stdClass $rec (the record in the database for which the payment was received)
 *
 * @return void
 */
function enrol_cielo_coursepaidevent($rec) {

    $context = context_course::instance($rec->courseid);

    $data = (array) $rec;

    $param = array(
        'context' => $context,
        'other' => $data,
    );

    $event = \enrol_cielo\event\payment_receive::create($param);
    $event->trigger();
}

