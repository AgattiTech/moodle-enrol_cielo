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

$string['assignrole'] = 'Atribuir papel';
$string['enrolboleto'] = 'Habilitar boleto';
$string['enrolboleto_desc'] = 'Quando ticado, então é habilitado o formulário de pagamento através de boleto';
$string['merchantid'] = 'Cielo Merchant ID';
$string['merchantid_desc'] = 'O Merchant ID pode ser obtido através do sistema da Cielo para uso de sua API';
$string['merchantkey'] = 'Cielo Merchant Key';
$string['merchantkey_desc'] = 'O Merchant Key pode ser obtido através do sistema da Cielo para uso de sua API';
$string['cost'] = 'Custo da Inscrição';
$string['installments'] = 'Máximo de parcelas';
$string['costerror'] = 'O custo da inscrição não está em formato numérico';
$string['costorkey'] = 'Por favor escolha um dos métodos de inscrição.';
$string['currency'] = 'Moeda';
$string['currency_desc'] = 'Moeda Brasileira : Real Brasileiro';
$string['defaultrole'] = 'Papel padrão';
$string['defaultrole_desc'] = 'Selecionar o papel padrão a ser atribuído aos usuários durante a inscrição através da Cielo.';
$string['enrolenddate'] = 'Data final';
$string['enrolenddate_help'] = 'Se habilitado usuários só poderão ficar inscritos até essa data.';
$string['enrolenddaterror'] = 'A data de término da inscrição não pode ser anterior à data de início';
$string['enrolperiod'] = 'Duração da inscrição';
$string['enrolperiod_desc'] = 'Período de tempo padrão que a inscrição é válida (em segundos). Se definido como zero, a duração da inscrição será ilimitada por padrão.';
$string['enrolperiod_help'] = 'Tempo de validade da inscrição, a partir do momento em que o usuário se inscreve. Se desativado, a duração da inscrição será ilimitada. ';
$string['enrolstartdate'] = 'Data de Início';
$string['enrolstartdate_help'] = 'Se ativado, os usuários podem ser inscritos apenas a partir desta data. ';
$string['error:naoautorizado'] = 'Este site não está autorizado a usar a API Cielo.';
$string['error:cartaoexpirado'] = 'Seu cartão expirou.';
$string['error:cartaobloqueado'] = 'Não foi possível autorizar a transação, entre em contato com o emissor do cartão.';
$string['error:timeout'] = 'Ocorreu um erro de tempo limite, tente novamente mais tarde.';
$string['error:cartaocancelado'] = 'Não foi possível autorizar a transação, entre em contato com o emissor do cartão.';
$string['error:problemascomcartao'] = 'Não foi possível autorizar a transação, entre em contato com o emissor do cartão. ';
$string['error:outro'] = 'Ocorreu um problema com o site, entre em contato com o administrador do sistema.';
$string['error:unauthorized'] = 'Este site não está autorizado a usar a API Cielo.';
$string['mailadmins'] = 'Notificar admin';
$string['mailfromsupport'] = 'Enviar emails do suporte';
$string['mailfromsupport_desc'] = 'Se marcada, o e-mail de suporte será usado como remetente, caso contrário, o e-mail do professor será usado.';
$string['mailstudents'] = 'Notificar alunos';
$string['mailteachers'] = 'Notificar professores';
$string['messageprovider:cielo_enrolment'] = 'Mensagens de inscrição da Cielo';
$string['needsignuporlogin'] = 'Você precisa se inscrever ou fazer login antes de fazer um pagamento.';
$string['nocost'] = 'Não há custo associado à inscrição neste curso!';
$string['cielo:config'] = 'Configurar instâncias de inscrição da Cielo ';
$string['cielo:manage'] = 'Gerenciar usuários inscritos';
$string['cielo:unenrol'] = 'Cancelar inscrição de usuários do curso ';
$string['cielo:unenrolself'] = 'Auto cancelar a inscrição do curso';
$string['cieloaccepted'] = 'Aceita pagamentos Cielo';
$string['paymentrequiredp1'] = 'Você deve fazer um pagamento de {$a->currency} ';
$string['paymentrequiredp2'] = ' via Cielo para acessar este curso.';
$string['pluginname'] = 'Cielo';
$string['pluginname_desc'] = 'O módulo Cielo permite a configuração de cursos pagos. Se o custo de qualquer curso for zero, os alunos não serão solicitados a pagar pela inscrição. Há um custo que você define aqui como padrão para todo o site e, em seguida, uma configuração de curso que pode ser definida para cada curso individualmente. O custo do curso substitui o custo do site.';
$string['sendpaymentbutton'] = 'Enviar pagamento via Cielo';
$string['sendpaymentbuttonrecurrent'] = 'Enviar pagamento via Cielo Recorrente';
$string['status'] = 'Permitir inscrições Cielo';
$string['status_desc'] = 'Permitir que os usuários usem a Cielo para se inscrever em um curso por padrão.';
$string['unenrolselfconfirm'] = 'Você realmente deseja cancelar a sua inscrição no curso "{$a}"?';
$string['transparentcheckout'] = 'Checkout transparente ';
$string['usesandbox'] = 'Usar Sandbox';
$string['usesandboxdesc'] = 'Marque esta opção se você deseja usar uma conta sandbox (as solicitações serão enviadas para o site de teste do sandbox da Cielo em vez do site de produção)';
$string['paymentshowboleto'] = 'Obrigado pelo seu interesse! Por favor, veja o bilhete em anexo para pagamento. Após o pagamento você estará inscrito para entrar no curso "{$a->fullname}". Se você tiver qualquer problema, avise o professor ou o administrador do site';
$string['boletoemailsubject'] = 'Boleto Curso {$a->coursesn}';
$string['boletoemail'] = 'Obrigado pelo seu interesse no {$a->course}. Clique no link abaixo para visualizar o tíquete para pagamento: <br> <a href={$a->boletourl}> Ver boleto </a> <br> Caso o link não funcione você pode copiar e colar o endereço no seu navegador: <br>
{$a->boletourl}
<br>

Você também pode pagar com o número do bilhete: <br>
{$a->boletonum}';
$string['isrecurrent'] = 'Este método de pagamento é recorrente?';
$string['checkedyesno'] = 'Sim';
$string['recurringinterval'] = 'Com que frequência o aluno será cobrado?';
$string['cost_help'] = 'Caso seja selecionado o pagamento recorrente, este será o valor cobrado a cada parcela. ';
$string['expiredaction'] = 'Ação de expiração de inscrição';
$string['expiredaction_help'] = 'Selecione a ação a ser executada quando a inscrição do usuário expirar. Observe que alguns dados e configurações do usuário são eliminados do curso durante o cancelamento da inscrição.';
