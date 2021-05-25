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

    $(".cielo-payment-btn").on('click', function(){
        var couponcode = $(".cielo-payment-btn").data('coupon-code');
        $('#cc_couponcode').val(couponcode);
        createMasks();
    });

    $(document).on('click', '#cc_cielo_submit', function() {
        if(ccCieloValidateFields()){
            var urlParams = new URLSearchParams(window.location.search);
            $("#cc_courseid").val(urlParams.get('id'));
            $('#cielo_cc_form').submit();
        }
    });
    
    $(document).on('click', '#boleto_cielo_submit', function() {
        if(ccCieloValidateFields()){
            var urlParams = new URLSearchParams(window.location.search);
            $("#boleto_courseid").val(urlParams.get('id'));
            $('#cielo_boleto_form').submit();
        }
    });
    
    $(document).on('click', '#recurrentcc_cielo_submit', function() {
        if(ccCieloValidateFields()){
            var urlParams = new URLSearchParams(window.location.search);
            $("#recurrentcc_courseid").val(urlParams.get('id'));
            $('#cielo_recurrentcc_form').submit();
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
    
    $(document).on('focusout', '#boletocep', function() {
        var cep = $('#boletocep').val().replace(/\D/g, '');
        // Verifica se campo cep possui valor informado.
        if (cep != "") {
            // Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;
            // Valida o formato do CEP.
            if(validacep.test(cep)) {
                // Preenche os campos com "..." enquanto consulta webservice.
                $("#boletologradouro").val("...");
                $("#boletobairro").val("...");
                $("#boletocidade").val("...");
                $("#boletouf").val("...");
                // Consulta o webservice viacep.com.br.
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                    if (!("erro" in dados)) {
                        // Atualiza os campos com os valores da consulta.
                        $("#boletologradouro").val(dados.logradouro);
                        $("#boletobairro").val(dados.bairro);
                        $("#boletocidade").val(dados.localidade);
                        $("#boletouf").val(dados.uf);
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
    
    $(document).on('focusout', '#recurrentcccep', function() {
        var cep = $('#recurrentcccep').val().replace(/\D/g, '');
        // Verifica se campo cep possui valor informado.
        if (cep != "") {
            // Expressão regular para validar o CEP.
            var validacep = /^[0-9]{8}$/;
            // Valida o formato do CEP.
            if(validacep.test(cep)) {
                // Preenche os campos com "..." enquanto consulta webservice.
                $("#recurrentcclogradouro").val("...");
                $("#recurrentccbairro").val("...");
                $("#recurrentcccidade").val("...");
                $("#recurrentccuf").val("...");
                // Consulta o webservice viacep.com.br.
                $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                    if (!("erro" in dados)) {
                        // Atualiza os campos com os valores da consulta.
                        $("#recurrentcclogradouro").val(dados.logradouro);
                        $("#recurrentccbairro").val(dados.bairro);
                        $("#recurrentcccidade").val(dados.localidade);
                        $("#recurrentccuf").val(dados.uf);
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
    });
}

function limpa_formulário_cep_boleto() {
    // Limpa valores do formulário de cep.
    require(['jquery'],function($){
        $("#boletologradouro").val("");
        $("#boletobairro").val("");
        $("#boletocidade").val("");
        $("#boletouf").val("");
    });
}

function ccCieloValidateFields(){
    
    var rtn = true;
    require(['jquery'],function($) {
        var re_ccvalid = /\d{2}\/\d{4}/gm;
        var re_ccNumber = /\d{4}(\s\d{4}){3}/gm;
        var re_cccvv = /\d{3}/gm;
        if(!$('input[name=ccbrand]:checked', '#cielo_cc_form').val()){
            rtn = false;
            $("#ccBrand-error").html('Favor escolher bandeira do cartão');
        } else{
            $("#ccBrand-error").html('');
        }
        if(!$("#ccName").val().trim()){
            rtn = false;
            $("#ccName-error").html('Favor preencher Nome corretamente');
        }else{
            $("#ccName-error").html('');
        }
        if(!re_ccNumber.test($("#ccNumber").val().trim())){
            rtn = false;
            $("#ccNumber-error").html('Favor preencher Número do cartão corretamente');
        }else{
            $("#ccNumber-error").html('');
        }
        if(!re_ccvalid.test($("#ccvalid").val().trim())){
            console.log("reached here");
            rtn = false;
            $("#ccvalid-error").html('Favor preencher Validade do cartão corretamente mm/aaaa');
        }else{
            $("#ccvalid-error").html('');
        }
        if(!re_cccvv.test($("#cvv").val().trim())){
            rtn = false;
            $("#cvv-error").html('Favor preencher CVV corretamente');
        }else{
            $("#cvv-error").html('');
        }
    });

    return rtn;
}
