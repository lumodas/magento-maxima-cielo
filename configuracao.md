# Configuração #

Por padrão, os métodos de pagamento Cartão de Débito e Crédito vêm desativados. Para ativá-los, você deve ir ao menu **Sistema -> Configuração -> Métodos de Pagamento** e editar as configurações básicas nas abas Cartão de Débito Cielo e Cartão de Crédito Cielo.

Os parâmetros básicos são listados abaixo:

  * Ativado: flag que ativa ou desativa este método de pagamento;
  * Modalidade: aqui deve ser escolhido entre Buy Page Cielo e Buy Page Loja. Buy Page Cielo faz com que, no fim da compra, o site seja redirecionado para o ambiente da Cielo para que o cliente realize o pagamento. Buy Page Loja faz a coleta de dados do pagamento ainda na loja e se comunica com o Web Service da Cielo para passar os dados;
  * Título: título que irá aparecer no frontend do Magento quando o cliente estiver realizando uma compra;
  * Soft Descriptor: campo que permite a inserção de uma descrição que irá aparecer na fatura do cartão de crédito do cliente;
  * Ambiente: aqui escolhemos se o ambiente da Cielo que estamos utilizando é o ambiente de teste ou o ambiente de produção. Isto determina qual link (webservice) será utilizado pelo módulo;
  * Bandeira de Cartões: aqui devem ser escolhidas as bandeiras que estarão disponíveis no site;
  * Capturar Automaticamente: determina se a captura da compra será realizada automaticamente pelo sistema ou por meio da administração (na tela de edição da respectiva venda);
  * Autenticar: determina se, no caso das bandeiras Visa e Mastercard, haverá autenticação dos dados. Isto fará com que, no fim da compra, o cliente seja redirecionado para o ambiente da respectiva bandeira para coletar dados que autentiquem o proprietário do cartão;
  * Arquivo SSL: aqui devemos colocar o caminho do arquivo SSL no sistema de arquivos do servidor, de modo que ele não esteja em uma pasta acessível externamente, por motivos de segurança;
  * Status do Novo Pedido: status para o qual o pedido será colocado quando o cliente realizou a compra, mas ainda não efetuou o pagamento;
  * Núm. Inscrição Cielo: o número de inscrição do lojista na Cielo. Por padrão, vem o valor para o ambiente de testes da Cielo, disponibilizado na loja de exemplo por ela publicada;
  * Chave Cielo: a chave do lojista na Cielo. Por padrão, vem o valor para o ambiente de testes da Cielo, disponibilizado na loja de exemplo por ela publicada;
  * Num. máximo de parcelas: número máximo de parcelas permitido na hora de comprar um produto no frontend;
  * Tipo de parcelamento: forma de parcelamento da compra, pela Loja ou pela Administradora. A forma pela administradora tem muitas limitações (verifique as regras com a Cielo).
  * Valor mínimo das parcelas: determina o valor mínimo que deve possuir cada parcela, em caso de parcelamentos;
  * Parcelas sem juros: determina o número de parcelas às quais não devem ser aplicados juros, em caso de parcelamento;
  * Juros do pedido (%): percentual de juros que deve ser aplicado ao parcelamento. O módulo aplica a fórmula de juros compostos para determinar o valor de cada parcela.

## Modalidades ##

Por padrão, o módulo vem com as configurações do Buy Page Loja, mas caso queira alterar esta configuração, três campos devem ser alterados:

  * Modalidade: deve ser escolhido entre Buy Page Loja e Buy Page Cielo;
  * Núm. Inscrição Cielo: este dado, mesmo em ambientes de teste, é diferente em cada modalidade;
  * Chave Cielo: este dado, mesmo em ambientes de teste, é diferente em cada modalidade.


## Homologação ##

Quando for homologar a sua loja na Cielo, você deve alterar quatro configurações:

  * Ambiente: este deve ser passado para **Produção**.
  * Arquivo SSL: o arquivo SSL deve ser colocado em uma pasta que não possa ser acessível externamente, ou seja, não deve estar dentro da pasta **www** do Apache, muito menos na pasta do Magento. Assim, após colocá-lo em um local seguro, dê permissões de leitura para o mesmo e coloque o seu caminho neste campo da configuração, incluindo o nome do arquivo (por exemplo: /home/username/ssl/arquivo-cielo.ssl);
  * Núm. Inscrição Cielo: a Cielo irá lhe fornecer um número de inscrição do lojista adequado a ser colocado aqui;
  * Chave Cielo: a Cielo irá lhe fornecer uma chave do lojista adequada a ser colocada aqui.