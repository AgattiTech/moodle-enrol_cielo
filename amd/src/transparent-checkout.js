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
 * Potential user selector module.
 *
 * @module     enrol_manual/form-potential-user-selector
 * @class      form-potential-user-selector
 * @package    enrol_manual
 * @copyright  2016 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(['jquery'], function($){

    $("#cielo-payment-btn").on('click', function(){
        var couponcode = $("#cielo-payment-btn").data('coupon-code');
        $('#cc_couponcode').val(couponcode);
        createMasks();
    });

    $(document).on('click', '#cc_submit', function() {
        if(ccValidateFields()){
            var urlParams = new URLSearchParams(window.location.search);
            $("#cc_courseid").val(urlParams.get('id'));
            $('#cielo_cc_form').submit();
        }
    });

    $(document).on('focusout', '#billingpostcode', function() {
        var cep = $('#billingpostcode').val().replace(/\D/g, '');
        // Verifica se campo cep possui valor informado.
        if (cep != "") {
            // Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;
            // Valida o formato do CEP.
            if(validacep.test(cep)) {
                // Preenche os campos com "..." enquanto consulta webservice.
                $("#billingstreet").val("...");
                $("#billingdistrict").val("...");
                $("#billingcity").val("...");
                $("#billingstate").val("...");
                $("#ibge").val("...");
                // Consulta o webservice viacep.com.br.
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                    if (!("erro" in dados)) {
                        // Atualiza os campos com os valores da consulta.
                        $("#billingstreet").val(dados.logradouro);
                        $("#billingdistrict").val(dados.bairro);
                        $("#billingcity").val(dados.localidade);
                        $("#billingstate").val(dados.uf);
                        $("#ibge").val(dados.ibge);
                    } else {
                        // CEP pesquisado não foi encontrado.
                        limpa_formulário_cep();
                        alert("CEP não encontrado.");
                    }
                });
            } else {
                limpa_formulário_cep();
                alert("Formato de CEP inválido.");
            }
        } else {
            // Cep sem valor, limpa formulário.
            limpa_formulário_cep();
        }
    });
});

function createMasks(){
    require(['jquery', 'enrol_cielo/jqmask'], function($, jqmask){
        var ph_options = {
            onKeyPress: function(ph, e, field, ph_options){
                var masks = ['(00) 0000-00009', '(00) 0 0000-0000'];
                var mask = (ph.length > 14) ? masks[1] : masks [0];
                $('.input-phone').mask(mask, ph_options);
            }
        };
        $('.input-phone').mask("(00) 0000-0000", ph_options);
        var options = {
            onKeyPress: function(doc, e, field, options){
                var masks = ['000.000.000-009', '00.000.000/0000-00'];
                var mask = (doc.length > 14) ? masks[1] : masks [0];
                $('.input-cpfcnpj').mask(mask, options);
            }
        };
        $('.input-cpfcnpj').mask("000.000.000-009", options);
        $('.input-ccnumber').mask('0000 0000 0000 0000');
        $('.input-ccvalid').mask('00/0000');
        $('.input-cvv').mask('000');
    });
}

function limpa_formulário_cep() {
    // Limpa valores do formulário de cep.
    require(['jquery'],function($){
        $("#billingstreet").val("");
        $("#billingdistrict").val("");
        $("#billingcity").val("");
        $("#billingstate").val("");
        $("#ibge").val("");
    });
}

function ccValidateFields(){
    var rtn = true;
    require(['jquery'],function($){
        if(!$("#ccName").val().trim()){
            rtn = false;
            $("#ccName-error").html('Favor preencher Nome corretamente');
        }else{
            $("#ccName-error").html('');
        }
        if(!$("#ccNumber").val().trim()){
            rtn = false;
            $("#ccNumber-error").html('Favor preencher Número do cartão corretamente');
        }else{
            $("#ccNumber-error").html('');
        }
        if(!$("#ccvalid").val().trim()){
            rtn = false;
            $("#ccvalid-error").html('Favor preencher Validade do cartão corretamente');
        }else{
            $("#ccvalid-error").html('');
        }
        if(!$("#cvv").val().trim()){
            rtn = false;
            $("#cvv-error").html('Favor preencher CVV corretamente');
        }else{
            $("#cvv-error").html('');
        }
    });

    return rtn;
}
