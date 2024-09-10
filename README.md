# Firewall

Aplicação que permite liberar regras de acesso via nat reverso em firewalls mediante senha única.

Implementado para pfsense.

O sistema precisa ter acesso ssh por chave no pfsense.

Regras de nat:
* descrição deve ser no formato "codpes () Texto de descrição". Dentro do parêntesis será colocado a data da alteração
* orígem: host único ou alias. Colocar qualquer IP pois será alterado pelo sistema
* destino: colocar os dados de destino, principalmente intervalo de portas (geralmente será personalizado, uma única porta)
* dados do alvo: ip e porta do computador interno
* Criar uma regra de filtro associada

Procedimento de deploy padrão para Laravel
* senha única
* migrations
* etc

rodar `php artisan atualizarRemotos` é necessário copiar um script no pfsense (resourcers/pfsense/Pfsense-config3.php)

Gerar e configurar chave SSH
1. Gerar uma chave SSH

    Abra um terminal e execute: `ssh-keygen`
    Insira um caminho para a chave ou aperte enter para seguir com o caminho padrão

    Verifique as chaves geradas no diretório .ssh, se foi mantido o caminho padrão
    Se foi indicado outro caminho, as chaves geradas devem estar lá
    (a chave pública é a que possui a extensão de arquivo .pub)

2. Adicionar a chave pública ao pfSense

    Faça login na interface web do pfSense.
    Você deve adicionar sua chave SSH ao usuário admin através da página de gerenciamento de usuários. 
    Cole sua chave pública SSH na caixa de texto Chaves SSH autorizadas e clique em Salvar mais uma vez.

3. Configurar o caminho da chave privada

    Abra o arquivo .env em seu projeto e verifique o caminho indicado na variável pfsense_private_key
    Copie ambas as chaves para o caminho indicado dentro da pasta storage e renomeie como 'acesso-pfsense'.
    (padrão /firewall/storage/app)