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

require('../../config.php');
require_once("lib.php");
require_once($CFG->libdir.'/enrollib.php');
require_once($CFG->libdir.'/externallib.php');

require_login();

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

$paymentmethod = optional_param('pay_method', '', PARAM_RAW);

if ($paymentmethod == 'cc') {

    // Build array with all parameters from the form.
    $params = [];

    $courseid = optional_param('courseid', '0', PARAM_INT);
    $plugininstance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'cielo'));

    $params['courseid'] = $courseid;
    $params['instanceid'] = $plugininstance->id;

    $params['couponcode'] = optional_param('cc_couponcode', '', PARAM_RAW);

    // Continue building array of parameters from the form.
    $params['name'] = optional_param('ccholdername', '', PARAM_RAW);
    $params['desc'] = "AcademiaOdont";
    $params['amount'] = number_format($plugininstance->cost, 2);
    $params['amount'] = str_replace(',', '', $params['amount']);
    $params['cc_number'] = str_replace(' ','',optional_param('ccnumber', '', PARAM_RAW));
    $params['cc_installment_quantity'] = optional_param('ccinstallments', '', PARAM_RAW);
    $params['cc_expiration'] = optional_param('ccvalid', '', PARAM_RAW);
    $params['cc_cvv'] = optional_param('cvv', '', PARAM_RAW);
    $params['cc_brand'] = optional_param('ccbrand', '', PARAM_RAW);

    $params['payment_status'] = STATUS_PENDING;

    // Handle Credit Card Checkout.
    cielo_cc_checkout($params, $merchantid, $merchantkey, $baseurl);
}

/**
 * Controller function of the credit card checkout
 *
 * @param array $params array of information about the order, gathered from the form
 * @param string $email Pagseguro seller email
 * @param string $token Pagseguro seller token
 * @param string $baseurl defines if uses sandbox or production environment
 *
 * @return void
 */
function cielo_cc_checkout($params, $merchantid, $merchantkey, $baseurl) {
    //TODO: Store paymentID
    // First we insert the order into the database, so the customer's info isn't lost.
    $extraamount = cielo_checkcoupon($params);
    $params['extraamount'] = number_format($extraamount, 2);
    $refid = cielo_insertorder($params, $merchantid, $merchantkey);
    $params['reference'] = $refid;
    
    $total = (float) $params['extraamount'] + (float) $params['amount'];
    $params['total'] = $total;
    $reqjson = cielo_ccjson($params);
    
    $myfile = fopen("/var/www/moodle/enrol/cielo/log_data.txt", "w") or die("Unable to open file!");
    $txt = var_export($params, true);
    fwrite($myfile, $txt);
    fclose($myfile);

    $url = $baseurl."/1/sales";

    $data = cielo_sendpaymentdetails($reqjson, $url, $merchantid, $merchantkey);
    
    $transactionresponse = json_decode($data);
    
    $returncode = $transactionresponse->Payment->ReturnCode;

    if ($returncode != 4 && $returncode != 6) {
        $params['payment_status'] = STATUS_FAILURE;
        cielo_updateorder($params, $merchantid, $merchantkey);
        redirect(new moodle_url('/enrol/cielo/return.php', array('id' => $params['courseid'], 'errorcode' => $returncode)));
    }
    
    $captureresponse = cielo_captureccpayment($baseurl, $transactionresponse, $merchantid, $merchantkey);
    
    $rec = cielo_handlecaptureresponse(json_decode($captureresponse), $params['reference']);
    
    cielo_handleenrolment($rec);

    redirect(new moodle_url('/enrol/cielo/return.php', array('id' => $params['courseid'] )));
}

/**
 * Controller function of the notification receiver
 *
 * @param string $notificationcode the notification code sent by Pagseguro
 * @param string $email Pagseguro seller email
 * @param string $token Pagseguro seller token
 * @param string $baseurl defines if uses sandbox or production environment
 *
 * @return void
 */
function cielo_transparent_notificationrequest($notificationcode, $email, $token, $baseurl) {

    $url = $baseurl."/v3/transactions/notifications/{$notificationcode}?email={$email}&token={$token}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded; charset=ISO-8859-1"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $data = curl_exec($ch);
    curl_close($ch);

    $transaction = simplexml_load_string($data);

    $rec = cielo_transparent_handletransactionresponse($transaction);

    cielo_transparent_handleenrolment($rec);

}

function cielo_checkcoupon($params) {
    if (!empty($params['couponcode'])) {
//        $context = context_course::instance($params['courseid']);
//        external_api::validate_context($context);
        $args = array('couponcode' => $params['couponcode'], 'courseid' => $params['courseid'] );
        if (external_api::call_external_function('enrol_coupon_validate_coupon', $args)) {
            $args = array('couponcode' => $params['couponcode']);
            $coupon = external_api::call_external_function('enrol_coupon_get_coupon_by_code', $args);
            $couponvalue = $coupon['data']['coupondiscount'];
            if ($coupon['data']['coupontype'] == 'value') {
                $extraamount = -1 * $couponvalue;
            } else {
                $extraamount = -1 * (($couponvalue / 100) * $params['amount']);
            }
            return $extraamount;
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

/**
 * Sends payment details with an XML string to a URL using the curl request system.
 *
 * @param string $xml the XML file to be sent to URL
 * @param string $url
 *
 * @return mixed $data the response from the curl request
 */
function cielo_sendpaymentdetails($json, $url, $merchantid, $merchantkey) {

    $d = array($json,$url);
    $myfile = fopen("/var/www/moodle/enrol/cielo/log_req.txt", "w") or die("Unable to open file!");
    $txt = var_export($d, true);
    fwrite($myfile, $txt);
    fclose($myfile);
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $json,
      CURLOPT_HTTPHEADER => array(
        'MerchantId: '.$merchantid,
        'Content-Type: application/json',
        'MerchantKey: '.$merchantkey
      ),
    ));

    $data = curl_exec($curl);

    curl_close($curl);
    
    $myfile = fopen("/var/www/moodle/enrol/cielo/log_res.txt", "w") or die("Unable to open file!");
    $txt = var_export($data, true);
    fwrite($myfile, $txt);
    fclose($myfile);

    return $data;

}

function cielo_captureccpayment($baseurl, $transactionresponse, $merchantid, $merchantkey) {

    $url = $baseurl.'/1/sales/'.$transactionresponse->Payment->PaymentId.'/capture';
    
    $d = array($url, $transactionresponse);
    
    $myfile = fopen("/var/www/moodle/enrol/cielo/log_reqcapture.txt", "w") or die("Unable to open file!");
    $txt = var_export($d, true);
    fwrite($myfile, $txt);
    fclose($myfile);
    
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'PUT',
      CURLOPT_HTTPHEADER => array(
        'MerchantId: '.$merchantid,
        'Content-Type: text/json',
        'MerchantKey: '.$merchantkey,
        'Content-length: 0'
      ),
    ));

    $data = curl_exec($curl);

    curl_close($curl);
    
    $myfile = fopen("/var/www/moodle/enrol/cielo/log_rescapture.txt", "w") or die("Unable to open file!");
    $txt = var_export($data, true);
    fwrite($myfile, $txt);
    fclose($myfile);
    
    return $data;
    
}

/**
 * Inserts preliminary order information into enrol_cielo table.
 *
 * @param array $params information about the order, gathered from the form
 * @param string $email Pagseguro seller email
 * @param string $token Pagseguro seller token
 *
 * @return string ID of the record inserted
 */
function cielo_insertorder($params, $merchantid, $merchantkey) {
    global $USER, $DB;

    $rec = new stdClass();
    $rec->merchantid = $merchantid;
    $rec->merchantkey = $merchantkey;
    $rec->courseid = $params['courseid'];
    $rec->userid = $USER->id;
    $rec->instanceid = $params['instanceid'];
    $rec->date = date("Y-m-d H:i:s");
    $rec->grossamount = $params['amount'];
    $rec->discountedamount = $params['extraamount'];
    $rec->payment_status = STATUS_PENDING;

    return $DB->insert_record("enrol_cielo", $rec);
}

/**
 * Updates the order information in enrol_cielo table.
 *
 * @param array $params information about the order, gathered from the form or cielo notification
 * @param string $email Pagseguro seller email
 * @param string $token Pagseguro seller token
 *
 * @return void
 */
function cielo_updateorder($params, $merchantid, $merchantkey) {
    global $USER, $DB;

    $rec = new stdClass();
    $rec->id = $params['reference'];
    $rec->merchant_id = $merchantid;
    $rec->merchant_key = $merchantkey;
    $rec->courseid = $params['courseid'];
    $rec->userid = $USER->id;
    $rec->instanceid = $params['instanceid'];
    $rec->date = date("Y-m-d");
    $rec->payment_status = $params['payment_status'];

    $DB->update_record("enrol_cielo", $rec);

}

/**
 * Receives transaction data and updates the database.
 *
 * @param stdClass $data
 *
 * @return string $id the record id of the updated record in the database
 */
function cielo_handlecaptureresponse($data, $reference) {

    global $DB;
    
    try{
        $rec = new stdClass();
        $rec->id = $reference;
        $rec->status = intval($data->ReturnCode);

        switch($rec->status){
            case SUCESSO:
            case SUCESSO2:
                $rec->payment_status = STATUS_SUCCESS;
                break;
            case NAO_AUTORIZADO:
            case CARTAO_EXPIRADO:
            case CARTAO_BLOQUEADO:
            case TIME_OUT:
            case CARTAO_CANCELADO:
            case PROBLEMAS_COM_CARTAO:
                $rec->payment_status = STATUS_FAILURE;
                break;

        }

        $DB->update_record("enrol_cielo", $rec);

        $record = $DB->get_record("enrol_cielo", ['id' => $rec->id]);
        if ($record->payment_status == STATUS_SUCCESS) {
            enrol_cielo_coursepaidevent($record);
        }

        return $record;
    }
    catch(Exception $e){
        $exceptionparam = new stdClass();
        $exceptionparam->message = $e->getMessage();
        $exceptionparam->message .= $data;
        throw new moodle_exception($e->getMessage() . $data);
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
        case STATUS_SUCCESS:
            $plugin->enrol_user($plugininstance, $rec->userid, $plugininstance->roleid, $timestart, $timeend);
            break;
        case STATUS_FAILURE:
            $plugin->unenrol_user($plugininstance, $rec->userid);
            break;
    }

}

/**
 * Builds the xml to send bank ticket request to cielo
 *
 * @param array $params fields from the form and plugin settings.
 *
 * @return string of data in xml format
 */
function cielo_transparent_boletoxml($params) {
    return "<? xml version=\"1.0\" encoding=\"ISO-8859-1\" standalone=\"yes\" ?>
        <payment>
                <mode>default</mode>
                <method>boleto</method>
	            <sender>
                    <name>".$params['name']."</name>
                    <email>".$params['email']."</email>
                    <phone>
                        <areaCode>".$params['phone_area']."</areaCode>
                        <number>".$params['phone_number']."</number>
    	            </phone>
    	            <documents>
    	                <document>
    	                    <type>".$params['doc_type']."</type>
    	                    <value>".$params['doc_number']."</value>
		                </document>
		            </documents>
		            <hash>".$params['sender_hash']."</hash>
                </sender>
                <currency>".$params['currency']."</currency>
                <notificationURL>".$params['notification_url']."</notificationURL>
                <items>
                    <item>
                        <id>".$params['courseid']."</id>
                        <description>".$params['item_desc']."</description>
                        <amount>".$params['item_amount']."</amount>
                        <quantity>".$params['item_qty']."</quantity>
                    </item>
                </items>
                <extraAmount>".$params['extraamount']."</extraAmount>
                <reference>".$params['reference']."</reference>
                <shipping>
                    <addressRequired>false</addressRequired>
                </shipping>
            </payment>";
}

/**
 * Builds the xml to send credit card request to cielo
 *
 * @param array $params fields from the form and plugin settings.
 *
 * @return string of data in xml format
 */
function cielo_ccjson($params) {
    return '{
       "MerchantOrderId":"'.$params['reference'].'",
       "Payment":{
         "Type":"CreditCard",
         "Amount":'.$params['total'] .',
         "Installments":'.$params['cc_installment_quantity'].',
         "SoftDescriptor":"'.$params['desc'].'",
         "CreditCard":{
             "CardNumber":"'.$params['cc_number'].'",
             "Holder":"'.$params['name'].'",
             "ExpirationDate":"'.$params['cc_expiration'].'",
             "SecurityCode":"'.$params['cc_cvv'].'",
             "Brand":"'.$params['cc_brand'].'"
         }
       }
    }';
}


