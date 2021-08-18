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
 * @copyright  2021 Igor Agatti Lima <igor@igoragatti.com>
 * @author     Igor Agatti Lima based on code by Eugene Venter, Daniel Neis Araujo and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("lib.php");
require_once($CFG->libdir.'/enrollib.php');
require_once($CFG->libdir.'/externallib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

global $USER;

require_login();

define('SUCESSO', 4);
define('SUCESSO2', 6);
define('SUCESSO3', 0);
define('SUCESSO4', '00');
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

$usercpf = optional_param('cpf', '', PARAM_RAW);
$addresscep = optional_param('cep', '', PARAM_RAW);
$addresslogradouro = optional_param('logradouro', '', PARAM_RAW);
$addressbairro = optional_param('bairro', '', PARAM_RAW);
$addresscidade = optional_param('cidade', '', PARAM_RAW);
$addressuf = optional_param('uf', '', PARAM_RAW);
$addresscomplemento = optional_param('complemento', '', PARAM_RAW);
$addressnumero = optional_param('numero', '', PARAM_RAW);
$userphone = optional_param('phone', '', PARAM_RAW);

$instanceid = optional_param('instanceid','', PARAM_INT);
    
if($usercpf) {
    $USER->profile_field_cpf = $usercpf;
}
if($addresscep) {
    $USER->profile_field_cep = $addresscep;
}
if($addresslogradouro) {
    $USER->profile_field_logradouro = $addresslogradouro;
}
if($addressbairro) {
    $USER->profile_field_bairro = $addressbairro;
}
if($addresscidade) {
    $USER->profile_field_cidade = $addresscidade;
}
if($addressuf) {
    $USER->profile_field_uf = $addressuf;
}
if($addresscomplemento) {
    $USER->profile_field_complemento = $addresscomplemento;
}
if($addressnumero) {
    $USER->profile_field_numero = $addressnumero;
}

if($userphone) {
    $USER->phone1 = $userphone;
}

profile_save_data($USER);

$params = [];

$params['paymentmethod'] = $paymentmethod;
$params['instanceid'] = $instanceid;

$plugininstance = $DB->get_record('enrol', array('id' => $instanceid));

if ($paymentmethod == 'cc') {

    // Build array with all parameters from the form.
    

    $courseid = optional_param('courseid', '0', PARAM_INT);

    $params['courseid'] = $courseid;

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
    
    $params['cpf'] = $usercpf;
    $params['cep'] = $addresscep;
    $params['logradouro'] = $addresslogradouro;
    $params['bairro'] = $addressbairro;
    $params['cidade'] = $addresscidade;
    $params['uf'] = $addressuf;
    $params['complemento'] = $addresscomplemento;
    $params['numero'] = $addressnumero;

    $params['payment_status'] = STATUS_PENDING;

    // Handle Credit Card Checkout.
    cielo_cc_checkout($params, $merchantid, $merchantkey, $baseurl);
} elseif ($paymentmethod == 'boleto') {

    // Build array with all parameters from the form.

    $courseid = optional_param('courseid', '0', PARAM_INT);

    $params['courseid'] = $courseid;

    $params['couponcode'] = optional_param('cc_couponcode', '', PARAM_RAW);

    // Continue building array of parameters from the form.
    $params['name'] = optional_param('ccholdername', '', PARAM_RAW);
    $params['desc'] = "AcademiaOdont";
    $params['amount'] = number_format($plugininstance->cost, 2);
    $params['amount'] = str_replace(',', '', $params['amount']);
    $params['expiration'] = date("Y-m-d", strtotime('+5 days'));
    
    $params['cpf'] = $usercpf;
    $params['cep'] = $addresscep;
    $params['logradouro'] = $addresslogradouro;
    $params['bairro'] = $addressbairro;
    $params['cidade'] = $addresscidade;
    $params['uf'] = $addressuf;
    $params['complemento'] = $addresscomplemento;
    $params['numero'] = $addressnumero;

    $params['payment_status'] = STATUS_PENDING;

    // Handle Credit Card Checkout.
    cielo_boleto_checkout($params, $merchantid, $merchantkey, $baseurl);

} elseif ($paymentmethod == 'recurrentcc') {
    $courseid = optional_param('courseid', '0', PARAM_INT);

    $params['courseid'] = $courseid;

    $params['couponcode'] = optional_param('cc_couponcode', '', PARAM_RAW);

    // Continue building array of parameters from the form.
    $params['name'] = optional_param('ccholdername', '', PARAM_RAW);
    $params['desc'] = "AcademiaOdont";
    $params['amount'] = number_format($plugininstance->cost, 2);
    $params['amount'] = str_replace(',', '', $params['amount']);
    $params['cc_number'] = str_replace(' ','',optional_param('ccnumber', '', PARAM_RAW));
    $params['cc_expiration'] = optional_param('ccvalid', '', PARAM_RAW);
    $params['cc_cvv'] = optional_param('cvv', '', PARAM_RAW);
    $params['cc_brand'] = optional_param('ccbrand', '', PARAM_RAW);
    
    $params['cpf'] = $usercpf;
    $params['cep'] = $addresscep;
    $params['logradouro'] = $addresslogradouro;
    $params['bairro'] = $addressbairro;
    $params['cidade'] = $addresscidade;
    $params['uf'] = $addressuf;
    $params['complemento'] = $addresscomplemento;
    $params['numero'] = $addressnumero;
    
    //recurrent options
    $params['interval'] = $plugininstance->customtext1;
    $params['enddate'] = $plugininstance->enrolenddate ? date("Y-m-d", $plugininstance->enrolenddate) : '';

    $params['payment_status'] = STATUS_PENDING;

    cielo_recurrentcc_checkout($params, $merchantid, $merchantkey, $baseurl);

}

/**
 * Controller function of the credit card checkout
 *
 * @param array $params array of information about the order, gathered from the form
 * @param string $email Cielo seller email
 * @param string $token Cielo seller token
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
    
//    $myfile = fopen("log_data.txt", "w") or die("Unable to open myfile!");
//    $txt = var_export($params, true);
//    fwrite($myfile, $txt);
//    fclose($myfile);

    $url = $baseurl."/1/sales";

    $data = cielo_sendpaymentdetails($reqjson, $url, $merchantid, $merchantkey);
    
    $transactionresponse = json_decode($data);
    
    $params['logresponse'] = var_export($transactionresponse,true);
    
    $returncode = $transactionresponse->Payment->ReturnCode;
    $params['paymentid'] = $transactionresponse->Payment->PaymentId;

    if ($returncode != 4 && $returncode != 6 && $returncode != 0 && $returncode != '00') {
        $params['payment_status'] = STATUS_FAILURE;
        cielo_updateorder($params, $merchantid, $merchantkey);
        redirect(new moodle_url('/enrol/cielo/return.php', array('id' => $params['courseid'], 'type' => 'cc', 'errorcode' => $returncode)));
    }
    
    $captureresponse = cielo_captureccpayment($baseurl, $transactionresponse, $merchantid, $merchantkey);
    
    $rec = cielo_handlecaptureresponse(json_decode($captureresponse), $params, $merchantid, $merchantkey);
    
    cielo_handleenrolment($rec, $params);

    redirect(new moodle_url('/enrol/cielo/return.php', array('id' => $params['courseid'], 'type' => 'cc' )));
}

/**
 * Controller function of the credit card checkout
 *
 * @param array $params array of information about the order, gathered from the form
 * @param string $email Cielo seller email
 * @param string $token Cielo seller token
 * @param string $baseurl defines if uses sandbox or production environment
 *
 * @return void
 */
function cielo_boleto_checkout($params, $merchantid, $merchantkey, $baseurl) {
    //TODO: Store paymentID
    // First we insert the order into the database, so the customer's info isn't lost.
    $extraamount = cielo_checkcoupon($params);
    $params['extraamount'] = number_format($extraamount, 2);
    $refid = cielo_insertorder($params, $merchantid, $merchantkey);
    $params['reference'] = $refid;
    
    $total = (float) $params['extraamount'] + (float) $params['amount'];
    $params['total'] = $total;
    
    $reqjson = cielo_boletojson($params);
    
//    $myfile = fopen("log_data.txt", "w") or die("Unable to open myfile!");
//    $txt = var_export($params, true);
//    fwrite($myfile, $txt);
//    fclose($myfile);

    $url = $baseurl."/1/sales";

    $data = cielo_sendpaymentdetails($reqjson, $url, $merchantid, $merchantkey);
    
    $transactionresponse = json_decode($data);
    
    $params['logresponse'] = var_export($transactionresponse,true);
    
    try{
        // send boleto via email
//        sendboletoemail($params);
        $params['boletonum'] = $transactionresponse->Payment->BarCodeNumber;
        $params['boletourl'] = $transactionresponse->Payment->Url;
        $params['paymentid'] = $transactionresponse->Payment->PaymentId;
        cielo_updateorder($params, $merchantid, $merchantkey);
        redirect(new moodle_url('/enrol/cielo/return.php', array('id' => $params['courseid'], 'type' => 'boleto', 'bUrl' => $params['boletourl'] )));
    
    } catch (Exception $e) {
        $params['payment_status'] = STATUS_FAILURE;
        cielo_updateorder($params, $merchantid, $merchantkey);
        redirect(new moodle_url('/enrol/cielo/return.php', array('id' => $params['courseid'], 'type' => 'boleto','errorcode' => "b1")));
    }

}

/**
 * Controller function of the credit card checkout
 *
 * @param array $params array of information about the order, gathered from the form
 * @param string $email Cielo seller email
 * @param string $token Cielo seller token
 * @param string $baseurl defines if uses sandbox or production environment
 *
 * @return void
 */
function cielo_recurrentcc_checkout($params, $merchantid, $merchantkey, $baseurl) {
    global $DB;
    //TODO: Store paymentID
    // First we insert the order into the database, so the customer's info isn't lost.
    $extraamount = cielo_checkcoupon($params);
    $params['extraamount'] = number_format($extraamount, 2);
    $refid = cielo_insertorder($params, $merchantid, $merchantkey);
    $params['reference'] = $refid;
    
    $total = (float) $params['extraamount'] + (float) $params['amount'];
    $params['total'] = $total;
    $reqjson = cielo_recurrentccjson($params);
    
//    $myfile = fopen("log_data.txt", "w") or die("Unable to open myfile!");
//    $txt = var_export($params, true);
//    fwrite($myfile, $txt);
//    fclose($myfile);

    $url = $baseurl."/1/sales";

    $data = cielo_sendpaymentdetails($reqjson, $url, $merchantid, $merchantkey);
    
    $transactionresponse = json_decode($data);
    
    $params['logresponse'] = var_export($transactionresponse,true);
    $params['paymentid'] = $transactionresponse->Payment->PaymentId;
    $params['recurrentpaymentid'] = $transactionresponse->Payment->RecurrentPayment->RecurrentPaymentId;
    
    $returncode = $transactionresponse->Payment->ReturnCode;

    if ($returncode != 4 && $returncode != 6 && $returncode != 0 && $returncode != '00') {
        $params['payment_status'] = STATUS_FAILURE;
        cielo_updateorder($params, $merchantid, $merchantkey);
        redirect(new moodle_url('/enrol/cielo/return.php', array('id' => $params['courseid'], 'type' => 'recurrentcc', 'errorcode' => $returncode)));
    }
    
    $captureresponse = cielo_captureccpayment($baseurl, $transactionresponse, $merchantid, $merchantkey);
    
    $rec = cielo_handlecaptureresponse(json_decode($captureresponse), $params, $merchantid, $merchantkey);
    
    cielo_handleenrolment($rec, $params);

    redirect(new moodle_url('/enrol/cielo/return.php', array('id' => $params['courseid'], 'type' => 'recurrentcc' )));
}

/**
 * Controller function of the notification receiver
 *
 * @param string $notificationcode the notification code sent by Cielo
 * @param string $email Cielo seller email
 * @param string $token Cielo seller token
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


function cielo_sendboletoemail($params){
    $a = new stdClass();
    $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
    $a->coursesn = format_string($course->shortname, true, array('context' => $coursecontext));
    $a->boletourl = $params['boletourl'];
    $a->boletonum = $params['boletonum'];
    $a->user = fullname($user);
    foreach ($admins as $admin) {
        $eventdata = new \core\message\message();
        $eventdata->component         = 'enrol_cielo';
        $eventdata->name              = 'cielo_enrolment';
        $eventdata->userfrom          = core_user::get_support_user();
        $eventdata->userto            = $user;
        $eventdata->subject           = get_string("boletoemailsubject", 'enrol_cielo', $a);
        $eventdata->fullmessage       = get_string('boletoemail', 'enrol_cielo', $a);
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml   = '';
        $eventdata->smallmessage      = '';

        message_send($eventdata);
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
//    $myfile = fopen("log_req.txt", "w") or die("Unable to open myfile!");
//    $txt = var_export($d, true);
//    fwrite($myfile, $txt);
//    fclose($myfile);
    
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
    
//    $myfile = fopen("log_res.txt", "w") or die("Unable to open myfile!");
//    $txt = var_export($data, true);
//    fwrite($myfile, $txt);
//    fclose($myfile);

    return $data;

}

function cielo_captureccpayment($baseurl, $transactionresponse, $merchantid, $merchantkey) {

    $url = $baseurl.'/1/sales/'.$transactionresponse->Payment->PaymentId.'/capture';
    
    $d = array($url, $transactionresponse);
    
//    $myfile = fopen("log_reqcapture.txt", "w") or die("Unable to open myfile!");
//    $txt = var_export($d, true);
//    fwrite($myfile, $txt);
//    fclose($myfile);
    
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
    
//    $myfile = fopen("log_rescapture.txt", "w") or die("Unable to open myfile!");
//    $txt = var_export($data, true);
//    fwrite($myfile, $txt);
//    fclose($myfile);
    
    return $data;
    
}

/**
 * Inserts preliminary order information into enrol_cielo table.
 *
 * @param array $params information about the order, gathered from the form
 * @param string $email Cielo seller email
 * @param string $token Cielo seller token
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
    $rec->type = $params['paymentmethod'];
    $rec->date = date("Y-m-d H:i:s");
    $rec->grossamount = $params['amount'];
    $rec->discountedamount = $params['extraamount'];
    $rec->payment_status = STATUS_PENDING;
    $rec->cpf = $params['cpf'];
    $rec->cep = $params['cep'];
    $rec->logradouro = $params['logradouro'];
    $rec->bairro = $params['bairro'];
    $rec->cidade = $params['cidade'];
    $rec->uf = $params['uf'];
    $rec->numero = $params['numero'];
    $rec->complemento = $params['complemento'];

    return $DB->insert_record("enrol_cielo", $rec);
}

/**
 * Updates the order information in enrol_cielo table.
 *
 * @param array $params information about the order, gathered from the form or cielo notification
 * @param string $email Cielo seller email
 * @param string $token Cielo seller token
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
    $rec->tid = $params['paymentid'] ?: null;
    $rec->recurrentpaymentid = $params['recurrentpaymentid'] ?: null;
    $rec->date = date("Y-m-d");
    $rec->payment_status = $params['payment_status'];
    $rec->request_string = $params['logresponse'];

    $DB->update_record("enrol_cielo", $rec);

}

/**
 * Receives transaction data and updates the database.
 *
 * @param stdClass $data
 *
 * @return string $id the record id of the updated record in the database
 */
function cielo_handlecaptureresponse($data, $params, $merchantid, $merchantkey) {

    global $DB;
    
    try{
        $rec = new stdClass();
        $rec->id = $params['reference'];
        $rec->status = intval($data->ReturnCode);

        switch($rec->status){
            case SUCESSO:
            case SUCESSO2:
            case SUCESSO03:
            case SUCESSO04:
                $params['payment_status'] = STATUS_SUCCESS;
                break;
            case NAO_AUTORIZADO:
            case CARTAO_EXPIRADO:
            case CARTAO_BLOQUEADO:
            case TIME_OUT:
            case CARTAO_CANCELADO:
            case PROBLEMAS_COM_CARTAO:
                $params['payment_status'] = STATUS_FAILURE;
                break;

        }
        
        cielo_updateorder($params, $merchantid, $merchantkey);

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
function cielo_handleenrolment($rec, $params) {
    global $DB;

    $plugin = enrol_get_plugin('cielo');
    $plugininstance = $DB->get_record('enrol', array('id' => $params['instanceid']));

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

/**
 * Builds the xml to send credit card request to cielo
 *
 * @param array $params fields from the form and plugin settings.
 *
 * @return string of data in xml format
 */
function cielo_boletojson($params) {
    return '{  
        "MerchantOrderId":"'.$params['reference'].'",
        "Customer":
        {  
            "Name":"'.$params['name'].'",
            "Identity": "'.$params['cpf'].'",
            "IdentityType":"'.$params['idtype'].'",
            "Address":
            {
              "Street": "'.$params['logradouro'].'",
              "Number": "'.$params['numero'].'",    
              "Complement": "'.$params['complemento'].'",
              "ZipCode" : "'.$params['cep'].'",
              "District": "'.$params['bairro'].'",
              "City": "'.$params['cidade'].'",
              "State" : "'.$params['uf'].'",
              "Country": "BRA"
            }
        },
        "Payment":
        {  
            "Type":"Boleto",
            "Amount":'.$params['total'] .',
            "Provider":"Bradesco",
            "Address": "RUA SANTA CATARINA, Nº 220, Complemento: SALA 101, Bairro: COMERCIARIO, CEP: 88802260, Cidade: Criciúma, Estado: Santa Catarina",
            "BoletoNumber": "'.$params['reference'].'",
            "Assignor": "Empresa Teste",
            "Demonstrative": "Curso na Academia da Odontologia",
            "ExpirationDate": "'.$params['expiration'].'",
            "Identification": "41504468000109",
                               
            "Instructions": "Aceitar somente até a data de vencimento."
        }
    }';
}

/**
 * Builds the xml to send credit card request to cielo
 *
 * @param array $params fields from the form and plugin settings.
 *
 * @return string of data in xml format
 */
function cielo_recurrentccjson($params) {
    return '{
       "MerchantOrderId":"'.$params['reference'].'",
       "Payment":{
         "Type":"CreditCard",
         "Amount":'.$params['total'] .',
         "Installments": 1,
         "SoftDescriptor":"'.$params['desc'].'",
         "RecurrentPayment":{
            "AuthorizeNow":"true",
            "EndDate":"'.$params['enddate'].'",
            "Interval":"'.$params['interval'].'"
         },
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


