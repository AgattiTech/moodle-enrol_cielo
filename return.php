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
 * PagSeguro return script.
 *
 * @package    enrol_pagseguro
 * @copyright  2020 Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require("../../config.php");
require_once("$CFG->dirroot/enrol/cielo/lib.php");

define('SUCCESS', 4);
define('SUCCESS2', 6);
define('NAO_AUTORIZADO', 05);
define('CARTAO_EXPIRADO', 57);
define('CARTAO_BLOQUEADO', 78);
define('TIME_OUT', 99);
define('CARTAO_CANCELADO', 77);
define('PROBLEMAS_COM_CARTAO', 70);

require_login();

$id = optional_param('id', 0, PARAM_INT);
$error = optional_param('errorcode', '4', PARAM_ALPHANUM);

if (!$course = $DB->get_record("course", array("id" => $id))) {
    redirect($CFG->wwwroot);
}

$context = context_course::instance($course->id);

if (isset($SESSION->wantsurl)) {
    $destination = $SESSION->wantsurl;
    unset($SESSION->wantsurl);
} else {
    $destination = "{$CFG->wwwroot}/course/view.php?id={$course->id}";
}

switch($error){
    case SUCCESS:
    case SUCCESS2:
        cielo_success($destination, $context, $course);
        break;
    case NAO_AUTORIZADO:
        cielo_error($destination, $context, 'naoautorizado');
        break;
    case CARTAO_EXPIRADO:
        cielo_error($destination, $context, 'cartaoexpirado');
        break;
    case CARTAO_BLOQUEADO:
        cielo_error($destination, $context, 'cartaobloqueado');
        break;
    case TIME_OUT:
        cielo_error($destination, $context, 'timeout');
        break;
    case CARTAO_CANCELADO:
        cielo_error($destination, $context, 'cartaocancelado');
        break;
    case PROBLEMAS_COM_CARTAO:
        cielo_error($destination, $context, 'problemascomcartao');
        break;
    default:
        cielo_error($destination, $context, 'outro');
        break;
}


function cielo_error($destination,$context, $errortype) {
    global $OUTPUT, $PAGE;
    $PAGE->set_context($context);
    $PAGE->set_url($destination);
    echo $OUTPUT->header();
    notice(get_string('error:'.$errortype, 'enrol_cielo'), $destination);
    echo $OUTPUT->footer();
}

function cielo_success($destination, $context, $course) {
    global $PAGE;
    
    $fullname = format_string($course->fullname, true, array('context' => $context));
    if (is_enrolled($context, null, '', true)) { // TODO: use real pagseguro check.
        redirect($destination, get_string('paymentthanks', '', $fullname));
    } else {
        $PAGE->set_context($context);
        $PAGE->set_url($destination);
        echo $OUTPUT->header();
        $a = new stdClass();
        $a->teacher = get_string('defaultcourseteacher');
        $a->fullname = $fullname;
        notice(get_string('paymentsorry', '', $a), $destination);
        echo $OUTPUT->footer();
    }
}


