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
 * Output rendering for the plugin.
 *
 * @package     Coupon
 * @copyright   2017 Damyon Wiese
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_cielo\output;

use plugin_renderer_base;
use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use moodle_url;
use enrol_coupon\data\datalib;

defined('MOODLE_INTERNAL') || die();

/**
 * Implements the plugin renderer
 *
 * @copyright 2017 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    public function show_boleto_frame($urlboleto){
        $attributesiframe = array('src' => $urlboleto);
        $linkstyle = "border-radius:15px;box-shadow:0 1px 3px #666666;color:#ffffff;font-size:20px;padding:10px 20px;margin: 0 auto; display:block; width: 135px;";
        $attributeslink = array('style' => $linkstyle, 'class' => 'btn btn-primary', 'target' => '_blank');
        $link = html_writer::link($urlboleto, 'Ver boleto', $attributeslink);
        return '<h3>Boleto Bancário</h3><br>'.$link.'<br>';
    }

}
