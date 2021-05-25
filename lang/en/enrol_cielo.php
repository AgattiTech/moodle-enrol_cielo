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
 * Strings for component 'enrol_cielo', language 'en'.
 *
 * @package    enrol_cielo
 * @copyright  2020 Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['assignrole'] = 'Assign role';
$string['automaticenrolboleto'] = 'Cielo Automatic enrol bill';
$string['automaticenrolboleto_desc'] = 'Automatic enrol by payment of bill';
$string['merchantid'] = 'Cielo Merchant ID';
$string['merchantid_desc'] = 'The Merchant ID obtained from Cielo to be able to use the API';
$string['merchantkey'] = 'Cielo merchant key';
$string['merchantkey_desc'] = 'The Merchant Key obtained from Cielo to be able to use the API';
$string['cost'] = 'Enrol cost';
$string['installments'] = 'Maximum installments';
$string['costerror'] = 'The enrolment cost is not numeric';
$string['costorkey'] = 'Please choose one of the following methods of enrolment.';
$string['currency'] = 'Currency';
$string['currency_desc'] = 'Brazil currency : Brazilian Real';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during Cielo enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid (in seconds). If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';
$string['error:naoautorizado'] = 'This host is not authorized to use Cielo API.';
$string['error:cartaoexpirado'] = 'Your card is expired.';
$string['error:cartaobloqueado'] = 'Unable to authorize transaction, please contact your card issuer.';
$string['error:timeout'] = 'There has been a timeout error, please try again later.';
$string['error:cartaocancelado'] = 'Unable to authorize transaction, please contact your card issuer.';
$string['error:problemascomcartao'] = 'Unable to authorize transaction, please contact your card issuer.';
$string['error:outro'] = 'There has been a problem with the site, please contact system administrator.';
$string['error:unauthorized'] = 'This host is not authorized to use Cielo API.';
$string['mailadmins'] = 'Notify admin';
$string['mailfromsupport'] = 'Send emails from support';
$string['mailfromsupport_desc'] = 'If checked, the support email will be used as sender, otherwise the teacher\'s email will be used.';
$string['mailstudents'] = 'Notify students';
$string['mailteachers'] = 'Notify teachers';
$string['messageprovider:cielo_enrolment'] = 'Cielo enrolment messages';
$string['needsignuporlogin'] = 'You need to sign up or log in before make a payment.';
$string['nocost'] = 'There is no cost associated with enrolling in this course!';
$string['cielo:config'] = 'Configure Cielo enrol instances';
$string['cielo:manage'] = 'Manage enrolled users';
$string['cielo:unenrol'] = 'Unenrol users from course';
$string['cielo:unenrolself'] = 'Unenrol self from the course';
$string['cieloaccepted'] = 'Cielo payments accepted';
$string['paymentrequired'] = 'You must make a payment of {$a->currency} {$a->cost} via Cielo to access this course.';
$string['pluginname'] = 'Cielo';
$string['pluginname_desc'] = 'The Cielo module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.  There is a site-wide cost that you set here as a default for the whole site and then a course setting that you can set for each course individually. The course cost overrides the site cost.';
$string['sendpaymentbutton'] = 'Send payment via Cielo';
$string['sendpaymentbuttonrecurrent'] = 'Send payment via Cielo Recurrent';
$string['status'] = 'Allow Cielo enrolments';
$string['status_desc'] = 'Allow users to use Cielo to enrol into a course by default.';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['transparentcheckout'] = 'Transparent Checkout';
$string['usesandbox'] = 'Use Sandbox';
$string['usesandboxdesc'] = 'Check this if you want to use a sandbox account (requests will be sent to sandbox.cielo.uol.com.br test site instead of the production site)';
$string['paymentshowboleto'] = 'Thank you for your interest! Please see the attached ticket for payment. After the payment you will be registered to enter the course "{$a->fullname}". If you have any trouble, please alert the Teacher or the site administrator';
$string['boletoemailsubject'] = 'Boleto Course {a->coursesn}';
$string['boletoemail'] = 'Thank you for your interest in {a->course}. Please click the link below to view the ticket for payment: <br> <a href={a->boletourl}> Ver boleto </a><br> In case the link does not work you can copy and paste the address on your browser:<br>
{a->boletourl}
<br>

You can also pay with the ticket number:<br>
{a->boletonum}';
$string['isrecurrent'] = 'Is this payment method recurrent?';
$string['checkedyesno'] = 'Yes';
$string['recurringinterval'] = 'How often will the student be charged?';
$string['cost_help'] = 'If recurrent payment is selected, then this will be the value charged on every installment.';
