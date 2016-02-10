# Instalação #

Atualmente, a instalação deve ser realizada de forma manual, mas futuramente irei inserir a instalação do módulo no Magento Connect. Para realizar a instalação, os seguintes passos devem ser seguidos:

  1. Baixar os arquivos via SVN:

Na aba Fonte (ou Source) deste projeto há o link do repositório em que os arquivos estão localizados. Para baixar os arquivos basta ter um cliente SVN instalado e realizar o checkout do respositório, e posteriormente o update. Caso você não esteja familiarizado com o SVN, na Wikipedia temos uma lista de clientes bastante utilizados que pode dar uma idéia de por onde começar: http://pt.wikipedia.org/wiki/Subversion . O link do repositório também pode ser visualizado no seu navegador, afim de visualizar os arquivos e a estrutura de pasta, mas por motivos de organização e comodidade, recomendamos o uso de um cliente SVN.

> 2. Copiar os arquivos para a pasta de instalação do Magento:

Uma vez que os arquivos estejam no seu computador, basta copiá-los para a pasta base de instalação do Magento. Os arquivos estão organizados de forma que já serão alocados adequadamente nos seus respectivos caminhos, adequados ao seu correto funcionamento. Caso o Magento esteja em um servidor, você pode realizar esta cópia utilizando transferência via ftp ou ssh.

> 3. Limpe o cache e a sessão:

Para que seu módulo seja enxergado pelo Magento, recomendamos que limpe as pastas **InstalacaoMagento/var/cache** e **InstalacaoMagento/var/session**.

Pronto! Agora seu módulo está instalado e pronto para ser configurado / utilizado. Para assegurar-se de que ele esteja instalado, vá no backend do Magento, no Menu **Sistema -> Configuração -> Avançado** e verifique se ele está listado dentre os módulos em **Desativar Saída de Módulos**.

Caso ele não esteja listado, reveja os passos com atenção e se ainda sim o problema persistir, fique a vontade para entrar no grupo de discussão e pedir ajuda (http://groups.google.com/group/cielo-magento) ou mandar e-mail pedindo ajuda.