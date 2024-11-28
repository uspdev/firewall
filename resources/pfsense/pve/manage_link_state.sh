#!/bin/bash

# Seta link_down para todas as interfaces de rede da VM exceto a configurada na VLAN IGNORADA
# Adaptado para container também

# deve rodar no console do PVE

# Configuração: VLAN a ser ignorada nas operações de up/down
IGNORED_VLAN=106

# Verificar se foram passados dois parâmetros
if [[ $# -lt 2 || $# -gt 3 ]]; then
    echo "Uso: $0 <VMID> <up|down|status> [--dry-run]"
    exit 1
fi

VMID=$1
ACTION=$2
DRY_RUN=$3

# Validar se o VMID é válido
if ! qm status "$VMID" &>/dev/null; then
    echo "Erro: VMID $VMID não é válido ou a VM não existe."
    exit 1
fi

# Função para exibir o comando se for em modo dry-run
run_command() {
    if [[ "$DRY_RUN" == "--dry-run" ]]; then
        echo "Dry-run: $1"
    else
        eval "$1"
    fi
}

# Verificar se a ação é status
if [[ "$ACTION" == "status" ]]; then
    echo "Exibindo status das interfaces para a VM $VMID:"

    # Obter toda a configuração da VM de uma vez
    config=$(qm config $VMID)

    # Processar as interfaces da VM
    for net in $(echo "$config" | grep -oP '^net\d+'); do
        # Verificar se a interface possui a propriedade link_down
        if echo "$config" | grep -q "^$net.*link_down"; then
            echo "Interface $net está DOWN."
        else
            echo "Interface $net está UP."
        fi
    done
    exit 0
fi

# Validar a ação
if [[ "$ACTION" != "up" && "$ACTION" != "down" ]]; then
    echo "Ação inválida. Use 'up' para ativar, 'down' para desativar ou 'status' para verificar."
    exit 1
fi

# Converter ação para o valor esperado no Proxmox
if [[ "$ACTION" == "up" ]]; then
    LINK_STATE=""
else
    LINK_STATE="link_down=1"
fi

# Obter toda a configuração da VM de uma vez
config=$(qm config $VMID)

# Processar as interfaces da VM
for net in $(echo "$config" | grep -oP '^net\d+'); do
    # Obter a configuração completa da interface
    config_line=$(echo "$config" | grep "^$net")

    # Verificar se a interface tem a VLAN ignorada
    if echo "$config_line" | grep -q "tag=$IGNORED_VLAN"; then
        echo "Ignorando $net (tag=$IGNORED_VLAN)."
    else
        # Extrair parâmetros necessários (mac, queues, tag, bridge)
        bridge=$(echo "$config_line" | sed 's/.*bridge=\([^,]*\).*/\1/')
        mac=$(echo "$config_line" | sed 's/.*virtio=\([^,]*\).*/\1/')
        queues=$(echo "$config_line" | sed 's/.*queues=\([^,]*\).*/\1/')
        tag=$(echo "$config_line" | sed 's/.*tag=\([^,]*\).*/\1/')

        # Certificar-se de que a interface tem todos os parâmetros corretamente configurados
        if [[ -z "$bridge" || -z "$mac" || -z "$queues" || -z "$tag" ]]; then
            echo "Erro: Não foi possível extrair todos os parâmetros da interface $net."
            continue
        fi

        # Comando para modificar a configuração sem duplicação de parâmetros
        if [[ -n "$LINK_STATE" ]]; then
            command="qm set $VMID -$net virtio=$mac,bridge=$bridge,queues=$queues,tag=$tag,$LINK_STATE"
        else
            command="qm set $VMID -$net virtio=$mac,bridge=$bridge,queues=$queues,tag=$tag"
        fi

        # Exibir o comando em dry-run ou executar de fato
        run_command "$command"
        echo "Alterando estado do link de $net para $ACTION."
    fi
done

echo "Operação concluída para a VM $VMID com ação '$ACTION'."
