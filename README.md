# Firewall

Aplicação que permite liberar regras de acesso via nat reverso em firewalls mediante senha única.

Implementado para pfsense.

O sistema precisa ter acesso ssh por chave no pfsense.

É necessário copiar um script no pfsense (resourcers/pfsense/Pfsense-config3.php)

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
