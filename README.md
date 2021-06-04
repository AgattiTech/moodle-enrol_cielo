Plugin de inscrição via Cielo para o Moodle
-----------------------------------------------

Este plugin de inscrição permite que você venda seus cursos no Moodle através do Cielo.

Instalação
-------

Você deve colocar este código no diretório moodle/enrol/cielo

Você pode fazer o "git clone" deste repositório ou então fazer o download da útlima versão no link https://github.com/danielneis/moodle-enrol_cielo/archive/master.zip

Configuração
------------

* Primeiro, você deve criar um Token no site do Cielo para utilizar o plugin.
* Também no site do Cielo, você deve preencher a "URL de retorno Fixa" com a URL do seu site Moodle + o caminho para o script do plugin que processará o retorno. Deve ficar algo como: https://www.meumoodle.org/enrol/cielo/process.php . ATENÇÃO: Este link é só um exemplo, você deve substituir "www.meumoodle.org" com a URL do seu Moodle.
* Preencha também, mais abaixo, o campo "Notificação de transação" com a URL do seu site Moodle + o caminho para o script do plugin que processará o retorno. Deve ficar algo como: https://www.meumoodle.org/enrol/cielo/process.php. ATENÇÃO: Este link é só um exemplo, você deve substituir "www.meumoodle.org" com a URL do seu Moodle.
* Ainda no site do Cielo, você deve preencher o campo "Código de transação para página de redirecionamento" com o valor "transaction_id" (sem as aspas).
* Com o token criado, volte ao seu Moodle e habilite o plugin indo em "Bloco administração" > Administração do Site > Plugins > Inscrições > Gerenciar plugins de inscrições
* Acesse o link das configurações do plugin Cielo
* Preencha o campo de token com o token criado
* Agora você pode utilizar o método de inscrição Cielo nos cursos. Você deve ir em um curso, acessar o "Bloco Administração" > Usuários > Métodos de inscrição e lá adicionar o novo método "Cielo". Ao adicionar este método você poderá definir o valor do curso, a moeda de pagamento e o email associado com o Cielo que receberá os pagamentos.

Funcionalidades
---------------

* Para cada curso Moodle, você pode configura o valor que o usuário deve pagar para se inscrever.
* A inscrição é feita automaticamente no caso de pagamento via cartão de crétido.
* Não é feita a desinscrição do usuário após devolução do dinheiro no Cielo.
* A inscrição automática via boleto bancário é feita quando o boleto é gerado. Não é validada a compensação do boleto, de forma que o usurio deve ser desinscrito manualmente caso no pague o boleto.
 
Sandbox
-------

Para utilizar ambiente de testes do Cielo (http://sandbox.cielo.com.br/), marque a opção "Usar sandbox".
    
Perguntas Frequentes
--------------------

* Ao tentar comprar o curso pelo Cielo, recebo a mensagem: "This host is not authorized to use Cielo API"
* Isso quer dizer que você não configurou o Cielo com a URL do seu ambiente Moodle. Você deve seguir os passos de configuração e preencher corretamente os campos no site do Cielo. Note que se você estiver usando o SandBox, deve cadastrar seu Moodle tambm no SandBox, pois são ambientes diferentes.
 
