// assets/js/app.js

// ==========================================
// FUNÇÕES AUXILIARES GERAIS
// ==========================================
function mudarAbaPedido(aba) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    if(aba === 'manual') document.querySelectorAll('.tab-btn')[0].classList.add('active');
    else document.querySelectorAll('.tab-btn')[1].classList.add('active');

    document.getElementById('form-pedido').style.display = aba === 'manual' ? 'block' : 'none';
    document.getElementById('form-pedido-csv').style.display = aba === 'csv' ? 'block' : 'none';
}

// ==========================================
// LOGIN
// ==========================================
const loginForm = document.getElementById('login-form');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorEl = document.getElementById('login-error');
        errorEl.textContent = '';

        const formData = new FormData(loginForm);
        try {
            const res = await fetch('api/login.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (res.ok && data.sucesso) {
                if (data.usuario.perfil === 'motorista') {
                    window.location.href = 'app_motorista.php';
                } else {
                    window.location.href = 'dashboard.php';
                }
            } else {
                errorEl.textContent = data.erro || 'Erro ao autenticar';
            }
        } catch (err) {
            errorEl.textContent = 'Erro de conexão com o servidor';
        }
    });
}

// ==========================================
// NAVEGAÇÃO SPA
// ==========================================
const navItems = document.querySelectorAll('.nav-item');
const tabContents = document.querySelectorAll('.tab-content');
const pageTitle = document.getElementById('page-title');

navItems.forEach(item => {
    item.addEventListener('click', (e) => {
        const target = item.getAttribute('data-target');
        if (!target) return; // Deixa ir para link externo (Rastreamento)

        e.preventDefault();
        navItems.forEach(n => n.classList.remove('active'));
        tabContents.forEach(t => t.classList.remove('active'));
        item.classList.add('active');
        
        document.getElementById(target)?.classList.add('active');
        pageTitle.textContent = item.innerText.trim();

        if (target === 'frota-content') loadVeiculos();
        if (target === 'motoristas-content') { loadVeiculos(); loadMotoristas(); }
        if (target === 'pedidos-content') loadPedidos();
    });
});

// ==========================================
// RF02: VEÍCULOS
// ==========================================
async function loadVeiculos() {
    const tbody = document.querySelector('#table-veiculos tbody');
    const select = document.getElementById('select-veiculos');
    if (!tbody) return;

    const res = await fetch('api/veiculos.php');
    const veiculos = await res.json();

    tbody.innerHTML = veiculos.map(v => `
        <tr>
            <td><strong>${v.placa}</strong></td>
            <td>${v.tipo}</td>
            <td>${v.capacidade_kg}kg / ${v.volume_m3}m³</td>
            <td>${v.status}</td>
            <td>${new Date(v.proxima_manutencao).toLocaleDateString('pt-BR')}</td>
        </tr>
    `).join('');

    if (select) {
        select.innerHTML = '<option value="">Nenhum</option>' +
            veiculos.map(v => `<option value="${v.id}">${v.placa} (${v.tipo})</option>`).join('');
    }
}

const formVeiculo = document.getElementById('form-veiculo');
if (formVeiculo) {
    formVeiculo.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch('api/veiculos.php', { method: 'POST', body: new FormData(formVeiculo) });
        const data = await res.json();
        alert(data.sucesso ? 'Veículo cadastrado!' : (data.erro || 'Erro'));
        if (data.sucesso) { formVeiculo.reset(); loadVeiculos(); }
    });
}

// ==========================================
// RF03: MOTORISTAS
// ==========================================
async function loadMotoristas() {
    const tbody = document.querySelector('#table-motoristas tbody');
    if (!tbody) return;

    const res = await fetch('api/motoristas.php');
    const motoristas = await res.json();

    tbody.innerHTML = motoristas.map(m => `
        <tr>
            <td><strong>${m.nome}</strong><br><small style="color:#6b7280">${m.email || 'Sem login'}</small></td>
            <td>${m.categoria_cnh}</td>
            <td>${m.contato}</td>
            <td><span class="badge ${m.disponibilidade === 'Disponível' ? 'disponivel' : 'rota'}">${m.disponibilidade}</span></td>
            <td>${m.veiculo_placa || '-'}</td>
        </tr>
    `).join('');
}

const formMotorista = document.getElementById('form-motorista');
if (formMotorista) {
    formMotorista.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch('api/motoristas.php', { method: 'POST', body: new FormData(formMotorista) });
        const data = await res.json();
        
        if(data.sucesso) {
            alert('Motorista e Login cadastrados com sucesso!');
            formMotorista.reset(); 
            loadMotoristas();
        } else {
            alert(data.erro || 'Erro ao cadastrar');
        }
    });
}

// ==========================================
// RF04: PEDIDOS (MANUAL E CSV)
// ==========================================
async function loadPedidos() {
    const tbody = document.querySelector('#table-pedidos tbody');
    if (!tbody) return;

    try {
        const res = await fetch('api/pedidos.php');
        const pedidos = await res.json();
        window.listaPedidosAtual = pedidos;

        tbody.innerHTML = pedidos.map((p, index) => {
            const dataInicio = new Date(p.janela_inicio).toLocaleString('pt-BR');
            const dataFim = new Date(p.janela_fim).toLocaleString('pt-BR');

            let badgeStatusClass = 'rota';
            if (p.status === 'Pendente') badgeStatusClass = 'manutencao';
            if (p.status === 'Entregue') badgeStatusClass = 'disponivel';

            let badgePrioridade = 'disponivel';
            if (p.prioridade === 'Alta') badgePrioridade = 'manutencao';
            if (p.prioridade === 'Normal') badgePrioridade = 'rota';

            let botaoPOD = '';
            if (p.status === 'Entregue' && p.comprovante_foto) {
                botaoPOD = `<br><button onclick="verPOD(${index})" style="margin-top:8px; font-size:0.75rem; cursor:pointer; background:#eff6ff; color:#2563eb; border:1px solid #2563eb; padding:4px 8px; border-radius:4px;">Ver Comprovante</button>`;
            }

            return `
                <tr>
                    <td><strong>${p.codigo}</strong></td>
                    <td>${p.cliente}<br><small style="color:#6b7280">${p.endereco}</small></td>
                    <td>${p.peso_kg} kg<br><small style="color:#6b7280">${p.volume_m3} m³</small></td>
                    <td><span class="badge ${badgePrioridade}">${p.prioridade}</span></td>
                    <td>${dataInicio}<br>até ${dataFim}</td>
                    <td><span class="badge ${badgeStatusClass}">${p.status}</span> ${botaoPOD}</td>
                </tr>
            `;
        }).join('');
        
    } catch (error) {
        console.error("Erro ao carregar pedidos:", error);
    }
}

const formPedido = document.getElementById('form-pedido');
if (formPedido) {
    formPedido.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch('api/pedidos.php', { method: 'POST', body: new FormData(formPedido) });
        const data = await res.json();
        
        if(data.sucesso) {
            alert(data.mensagem || 'Pedido criado!');
            formPedido.reset();
            loadPedidos();
        } else {
            alert(data.erro || 'Erro');
        }
    });
}

const formPedidoCsv = document.getElementById('form-pedido-csv');
if (formPedidoCsv) {
    formPedidoCsv.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = formPedidoCsv.querySelector('button');
        const originalText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importando...';

        const res = await fetch('api/pedidos.php', { method: 'POST', body: new FormData(formPedidoCsv) });
        const data = await res.json();
        
        btn.disabled = false;
        btn.innerHTML = originalText;

        if(data.sucesso) {
            alert(data.mensagem);
            formPedidoCsv.reset();
            loadPedidos();
            mudarAbaPedido('manual');
        } else {
            alert(data.erro || 'Erro ao importar CSV');
        }
    });
}

function verPOD(index) {
    const pedido = window.listaPedidosAtual[index];
    if (!pedido || !pedido.comprovante_foto) return alert('Comprovante não encontrado.');

    const win = window.open('', '_blank');
    win.document.write(`
        <html>
        <head>
            <title>Comprovante - ${pedido.codigo}</title>
            <style>
                body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f9fafb; }
                .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 500px; margin: auto; }
                h2 { color: #1f2937; margin-bottom: 5px; }
                p { color: #6b7280; margin-bottom: 15px; }
                img { width: 100%; border-radius: 8px; margin-top: 15px; border: 1px solid #e5e7eb; }
            </style>
        </head>
        <body>
            <div class="card">
                <h2>Comprovante de Entrega</h2>
                <p><strong>Pedido:</strong> ${pedido.codigo} - ${pedido.cliente}</p>
                <p><strong>Recebido por:</strong> ${pedido.comprovante_assinatura || 'Não assinado'}</p>
                <p><strong>Data/Hora:</strong> ${new Date(pedido.entregue_em).toLocaleString('pt-BR')}</p>
                <p><strong>Localização:</strong> Lat ${pedido.latitude}, Lng ${pedido.longitude}</p>
                <img src="${pedido.comprovante_foto}" alt="Foto da entrega">
                <br><br>
                <button onclick="window.close()" style="padding:10px; background:#ef4444; color:white; border:none; border-radius:6px; cursor:pointer; width:100%;">Fechar Janela</button>
            </div>
        </body>
        </html>
    `);
}

// ==========================================
// RF10: DASHBOARD GERENCIAL (GRÁFICOS E EXCEL)
// ==========================================
// Variáveis globais para o gráfico de previsão
let graficoPrevisaoInstance = null;
let dadosPrevisaoGlobal = null;

async function carregarGraficos() {
    try {
        // --- GRÁFICOS 1 E 2 (Status e Frota) ---
        const res = await fetch('api/dashboard_stats.php');
        if (!res.ok) throw new Error('Falha na API');
        const data = await res.json();

        const ctxStatus = document.getElementById('chart-status');
        if (ctxStatus) {
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Pendentes', 'Entregues'],
                    datasets: [{ data: [data.pedidos_pendentes, data.pedidos_entregues], backgroundColor: ['#f59e0b', '#10b981'], borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const ctxFrota = document.getElementById('chart-frota');
        if (ctxFrota) {
            new Chart(ctxFrota, {
                type: 'bar',
                data: {
                    labels: ['Disponíveis', 'Em Rota', 'Manutenção'],
                    datasets: [{ label: 'Veículos', data: [data.frota_disponivel, data.frota_rota, data.frota_manutencao], backgroundColor: ['#2563eb', '#8b5cf6', '#ef4444'], borderRadius: 6 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }

        // --- GRÁFICO 3 (Previsão de Demanda - RF08) ---
        try {
            const resPrevisao = await fetch('api/ia_previsao.php');
            if (!resPrevisao.ok) throw new Error('IA Offline');
            dadosPrevisaoGlobal = await resPrevisao.json();
            
            // Desenha o gráfico inicial (7 dias)
            desenharGraficoPrevisao('7dias');

            const projecaoEl = document.getElementById('projecao-30');
            if(projecaoEl) projecaoEl.innerHTML = `<strong>Projeção IA (30 dias):</strong> ~${dadosPrevisaoGlobal.projecao_30_dias} entregas`;

        } catch (error) {
            console.warn("IA de previsão offline. Usando fallback.");
            const projecaoEl = document.getElementById('projecao-30');
            if(projecaoEl) {
                projecaoEl.innerHTML = `<span style="color:#ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> A IA de previsão está temporariamente indisponível. Exibindo dados históricos apenas.</span>`;
            }
        }

    } catch (error) {
        console.error("Erro fatal ao carregar gráficos:", error);
        alert("Não foi possível carregar os dados do servidor. Verifique sua conexão.");
    }
}

// Função que desenha o gráfico dependendo do botão clicado
function desenharGraficoPrevisao(tipo) {
    const canvas = document.getElementById('chart-previsao');
    if (!canvas || !dadosPrevisaoGlobal) return;

    // Destrói o gráfico antigo antes de criar o novo
    if (graficoPrevisaoInstance) {
        graficoPrevisaoInstance.destroy();
    }

    let labels = [];
    let dataset = [];
    let labelTexto = '';
    let tipoGrafico = 'line';

    if (tipo === '7dias') {
        labels = dadosPrevisaoGlobal.previsao_7_dias.map(d => `${d.dia_semana} (${d.data})`);
        dataset = dadosPrevisaoGlobal.previsao_7_dias.map(d => d.quantidade);
        labelTexto = 'Pedidos Previstos (7 Dias)';
    } else {
        labels = dadosPrevisaoGlobal.previsao_mes.map(d => `Dia ${d.dia}`);
        dataset = dadosPrevisaoGlobal.previsao_mes.map(d => d.quantidade);
        labelTexto = 'Volume Estimado (Mês)';
        tipoGrafico = 'bar'; // Visão do mês em barras fica mais elegante
    }

    graficoPrevisaoInstance = new Chart(canvas, {
        type: tipoGrafico,
        data: {
            labels: labels,
            datasets: [{
                label: labelTexto,
                data: dataset,
                borderColor: '#8b5cf6',
                backgroundColor: tipoGrafico === 'bar' ? '#8b5cf6' : 'rgba(139, 92, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: tipo === '7dias' ? 4 : 0,
                borderRadius: tipoGrafico === 'bar' ? 4 : 0
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
}

// Função acionada pelos botões de alternância
function mudarVisaoPrevisao(tipo) {
    const btn7 = document.getElementById('btn-grafico-7dias');
    const btnMes = document.getElementById('btn-grafico-mes');

    if (tipo === '7dias') {
        btn7.style.background = 'white'; btn7.style.color = '#8b5cf6'; btn7.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        btnMes.style.background = 'transparent'; btnMes.style.color = '#6b7280'; btnMes.style.boxShadow = 'none';
    } else {
        btnMes.style.background = 'white'; btnMes.style.color = '#8b5cf6'; btnMes.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        btn7.style.background = 'transparent'; btn7.style.color = '#6b7280'; btn7.style.boxShadow = 'none';
    }

    desenharGraficoPrevisao(tipo);
}

async function exportarExcel() {
    const btn = document.querySelector('button[onclick="exportarExcel()"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando...';
    btn.disabled = true;

    try {
        const res = await fetch('api/dashboard_stats.php');
        const data = await res.json();

        let html = `
            <html><head><meta charset="utf-8"></head><body>
                <h2>Relatório Gerencial SENN AI-X</h2><p>Gerado em: ${new Date().toLocaleString('pt-BR')}</p>
                <table border="1">
                    <tr><th colspan="2" style="background-color: #f3f4f6;">Indicadores de Pedidos</th></tr>
                    <tr><td>Pedidos Pendentes</td><td>${data.pedidos_pendentes}</td></tr>
                    <tr><td>Pedidos Entregues</td><td>${data.pedidos_entregues}</td></tr>
                    <tr><th colspan="2" style="background-color: #f3f4f6;">Ocupação da Frota</th></tr>
                    <tr><td>Veículos Disponíveis</td><td>${data.frota_disponivel}</td></tr>
                    <tr><td>Veículos em Rota</td><td>${data.frota_rota}</td></tr>
                    <tr><td>Veículos em Manutenção</td><td>${data.frota_manutencao}</td></tr>
                </table>
            </body></html>
        `;
        
        const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = `relatorio_senn_${new Date().getTime()}.xls`; a.click();
        URL.revokeObjectURL(url);
    } catch (error) { alert('Erro ao gerar relatório.'); } finally {
        btn.innerHTML = originalText; btn.disabled = false;
    }
}

// ==========================================
// RF12: ANÁLISE INTELIGENTE DE DESEMPENHO
// ==========================================
function solicitarRelatorioIA() {
    const btn = document.getElementById('btn-relatorio-ia');
    const placeholder = document.getElementById('ia-placeholder');
    const resultados = document.getElementById('ia-resultados');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Analisando...';
    
    placeholder.style.display = 'block';
    placeholder.innerHTML = `
        <i class="fa-solid fa-microchip fa-fade" style="font-size: 3rem; color: #8b5cf6; margin-bottom: 15px;"></i>
        <p style="color: #8b5cf6; font-weight: bold;">Processando dados históricos...</p>
        <p style="font-size: 0.85rem;">A Inteligência Artificial está buscando gargalos e otimizações.</p>
    `;
    resultados.style.display = 'none';

    setTimeout(() => {
        carregarInsights();
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Atualizar Relatório';
        placeholder.style.display = 'none';
        resultados.style.display = 'block';
    }, 1500);
}

async function carregarInsights() {
    const container = document.getElementById('lista-insights');
    if (!container) return;

    try {
        const res = await fetch('api/ia_insights.php');
        const data = await res.json();

        container.innerHTML = data.insights.map(i => `
            <div style="display: flex; gap: 15px; background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb; animation: fadeIn 0.5s ease;">
                <div style="color: ${i.cor}; font-size: 1.5rem; margin-top: 2px;">
                    <i class="fa-solid ${i.icone}"></i>
                </div>
                <div style="font-size: 0.95rem; color: #374151; line-height: 1.5;">
                    ${i.texto}
                </div>
            </div>
        `).join('');

        const ctxRegioes = document.getElementById('chart-regioes');
        
        if (window.graficoRegioesInstance) {
            window.graficoRegioesInstance.destroy();
        }

        if (ctxRegioes && data.grafico_regioes && data.grafico_regioes.labels.length > 0) {
            window.graficoRegioesInstance = new Chart(ctxRegioes, {
                type: 'bar',
                data: {
                    labels: data.grafico_regioes.labels,
                    datasets: [{
                        label: 'Volume de Pedidos',
                        data: data.grafico_regioes.data,
                        backgroundColor: '#8b5cf6',
                        borderRadius: 6
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });
        }
    } catch (e) { 
        console.error("Erro nos insights", e); 
        container.innerHTML = '<p style="color: #ef4444;">Erro ao processar a IA.</p>';
    }
}

// ==========================================
// RF09: RISCOS DE ATRASO
// ==========================================
async function carregarRiscosIA() {
    const container = document.getElementById('painel-ia-risco');
    if (!container) return;

    try {
        const res = await fetch('api/ia_risco.php');
        const riscos = await res.json();

        if (riscos.length > 0) {
            container.style.display = 'block';
            document.getElementById('lista-riscos').innerHTML = riscos.map(r => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #fee2e2; background: #fef2f2; border-radius: 8px; margin-bottom: 8px;">
                    <div><strong>${r.codigo}</strong> - ${r.cliente}<br><small style="color: #991b1b;">${r.motivos.join(' | ')}</small></div>
                    <div style="text-align: right;"><span style="font-size: 1.2rem; font-weight: bold; color: #ef4444;">${r.risco}%</span><br><small style="color: #6b7280; font-size: 0.75rem;">💡 ${r.acao_sugerida}</small></div>
                </div>
            `).join('');
        }
    } catch (e) { console.error("Erro nos riscos", e); }
}

// ==========================================
// RF11: SISTEMA DE ALERTAS
// ==========================================
async function carregarAlertas() {
    const badge = document.getElementById('badge-alertas');
    if (!badge) return;

    try {
        const res = await fetch('api/alertas.php');
        const alertas = await res.json();

        if (alertas.length > 0) {
            badge.style.display = 'block';
            badge.textContent = alertas.length;

            const lista = document.getElementById('lista-alertas');
            lista.innerHTML = alertas.map(a => `
                <div style="padding:12px 15px; border-bottom:1px solid #f3f4f6; font-size:0.85rem; display:flex; align-items:start; gap:10px; justify-content:space-between;">
                    <div style="display:flex; gap:10px;">
                        <i class="fa-solid ${a.tipo === 'cnh' || a.tipo === 'pedido' ? 'fa-circle-exclamation' : 'fa-triangle-exclamation'}" style="color: ${a.tipo === 'cnh' || a.tipo === 'pedido' ? '#ef4444' : '#f59e0b'}; margin-top:3px;"></i>
                        <span style="color:#374151;">${a.mensagem}</span>
                    </div>
                    <button onclick="resolverAlerta(${a.id})" style="background:none; border:none; color:#10b981; cursor:pointer;" title="Marcar como resolvido"><i class="fa-solid fa-check-circle"></i></button>
                </div>
            `).join('');
        } else {
            badge.style.display = 'none';
            document.getElementById('lista-alertas').innerHTML = '<div style="padding:20px; text-align:center; color:#6b7280; font-size:0.85rem;">Nenhum alerta pendente. Tudo em dia!</div>';
        }
    } catch (e) { console.error('Erro ao buscar alertas', e); }
}

async function resolverAlerta(id) {
    const formData = new FormData();
    formData.append('id', id);
    
    const res = await fetch('api/resolver_alerta.php', { method: 'POST', body: formData });
    const data = await res.json();
    
    if(data.sucesso) {
        carregarAlertas();
    }
}

function toggleAlertas() {
    const painel = document.getElementById('painel-alertas');
    painel.style.display = painel.style.display === 'none' ? 'block' : 'none';
}

window.addEventListener('click', function(e) {
    const painel = document.getElementById('painel-alertas');
    const sino = document.querySelector('.fa-bell');
    if (painel && painel.style.display === 'block' && !painel.contains(e.target) && !sino.contains(e.target)) {
        painel.style.display = 'none';
    }
});

// ==========================================
// RF13: ASSISTENTE VIRTUAL (CHATBOT)
// ==========================================
const chatToggle = document.getElementById('chat-toggle');
const chatWindow = document.getElementById('chat-window');
const chatClose = document.getElementById('chat-close');
const chatForm = document.getElementById('chat-form');

if (chatToggle) {
    chatToggle.addEventListener('click', () => {
        chatWindow.style.display = chatWindow.style.display === 'none' ? 'flex' : 'none';
    });
    chatClose.addEventListener('click', () => {
        chatWindow.style.display = 'none';
    });
}

function enviarPerguntaRapida(texto) {
    const input = document.getElementById('chat-input');
    input.value = texto;
    const form = document.getElementById('chat-form');
    const event = new Event('submit', { cancelable: true });
    form.dispatchEvent(event);
}

if (chatForm) {
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('chat-input');
        const msg = input.value.trim();
        if (!msg) return;

        const messagesContainer = document.getElementById('chat-messages');
        
        messagesContainer.innerHTML += `
            <div style="background: #8b5cf6; color: white; padding: 10px; border-radius: 8px; font-size: 0.85rem; align-self: flex-end; max-width: 80%;">
                ${msg}
            </div>
        `;
        input.value = '';
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        const formData = new FormData();
        formData.append('mensagem', msg);

        try {
            const res = await fetch('api/ia_chat.php', { method: 'POST', body: formData });
            const data = await res.json();

            messagesContainer.innerHTML += `
                <div style="background: #e5e7eb; padding: 10px; border-radius: 8px; font-size: 0.85rem; align-self: flex-start; max-width: 80%;">
                    ${data.resposta}
                </div>
            `;
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        } catch (err) {
            messagesContainer.innerHTML += `<div style="color:red; font-size:0.8rem;">Erro ao conectar com a IA.</div>`;
        }
    });
}

// ==========================================
// INICIALIZAÇÃO DA PÁGINA
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    // 1. Chama os gráficos gerenciais
    if (document.getElementById('chart-status')) {
        carregarGraficos();
    }

    // 2. Chama os riscos de atraso
    if (document.getElementById('painel-ia-risco')) {
        carregarRiscosIA();
    }

    // 3. Chama o sino de alertas
    if (document.getElementById('badge-alertas')) {
        carregarAlertas();
        setInterval(carregarAlertas, 60000);
    }
});