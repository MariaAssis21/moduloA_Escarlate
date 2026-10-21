// assets/js/gestao_usuarios.js

document.addEventListener('DOMContentLoaded', () => {
    carregarUsuarios();
});

async function carregarUsuarios() {
    const tbody = document.getElementById('tbody-usuarios');
    if (!tbody) return;

    try {
        const res = await fetch('api/usuarios.php');
        const usuarios = await res.json();

        // Atualiza os cards do topo
        document.getElementById('total-usuarios').textContent = usuarios.length;
        document.getElementById('total-ativos').textContent = usuarios.filter(u => u.status === 'Ativo').length;

        tbody.innerHTML = usuarios.map(u => {
            // Formata a data
            const dataCadastro = new Date(u.criado_em).toLocaleDateString('pt-BR');
            
            // Cria as opções do Select de Perfil
            const perfis = ['admin', 'gestor', 'despachante', 'motorista'];
            const selectPerfil = `
                <select onchange="alterarPerfil(${u.id}, this.value)" style="padding: 6px; border-radius: 6px; border: 1px solid #ccc; background: #fff;">
                    ${perfis.map(p => `<option value="${p}" ${u.perfil === p ? 'selected' : ''}>${p.charAt(0).toUpperCase() + p.slice(1)}</option>`).join('')}
                </select>
            `;

            // Botões de Ação
            const btnStatus = u.status === 'Ativo' 
                ? `<button onclick="alterarStatus(${u.id}, 'Inativo')" title="Inativar" style="color: #f59e0b;"><i class="fa-solid fa-user-slash"></i></button>`
                : `<button onclick="alterarStatus(${u.id}, 'Ativo')" title="Ativar" style="color: #10b981;"><i class="fa-solid fa-user-check"></i></button>`;

            return `
                <tr>
                    <td>
                        <strong>${u.nome}</strong><br>
                        <small style="color: #6b7280;">${u.email}</small>
                        ${u.contato ? `<br><small style="color: #6b7280;"><i class="fa-solid fa-phone"></i> ${u.contato}</small>` : ''}
                    </td>
                    <td>${selectPerfil}</td>
                    <td class="${u.status === 'Ativo' ? 'status-ativo' : 'status-inativo'}">
                        <i class="fa-solid ${u.status === 'Ativo' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${u.status}
                    </td>
                    <td style="font-size: 0.9rem; color: #4b5563;">${dataCadastro}</td>
                    <td class="acoes">
                        ${btnStatus}
                        <button onclick="excluirUsuario(${u.id}, '${u.nome}')" title="Excluir" style="color: #ef4444;"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `;
        }).join('');

    } catch (e) {
        console.error("Erro ao carregar usuários", e);
    }
}

// Função para Mudar o Perfil (Permissão)
async function alterarPerfil(id, novoPerfil) {
    if (!confirm(`Tem certeza que deseja mudar a permissão deste usuário para ${novoPerfil.toUpperCase()}?`)) {
        carregarUsuarios(); // Recarrega para voltar o select ao normal se cancelar
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('acao', 'mudar_perfil');
    formData.append('perfil', novoPerfil);

    const res = await fetch('api/usuarios.php', { method: 'POST', body: formData });
    const data = await res.json();
    
    alert(data.sucesso ? data.msg : data.erro);
    carregarUsuarios();
}

// Função para Ativar/Inativar
async function alterarStatus(id, novoStatus) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('acao', 'mudar_status');
    formData.append('status', novoStatus);

    const res = await fetch('api/usuarios.php', { method: 'POST', body: formData });
    const data = await res.json();
    
    if(data.sucesso) carregarUsuarios();
    else alert(data.erro);
}

// Função para Excluir
async function excluirUsuario(id, nome) {
    if (!confirm(`CUIDADO: Deseja realmente EXCLUIR o usuário "${nome}"? Essa ação não pode ser desfeita.`)) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('acao', 'excluir');

    const res = await fetch('api/usuarios.php', { method: 'POST', body: formData });
    const data = await res.json();
    
    if(data.sucesso) carregarUsuarios();
    else alert(data.erro);
}