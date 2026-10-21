// assets/js/rastreamento.js
let map;
let markers = [];
let todosPedidos = [];
let mapaDisponivel = false;

document.addEventListener('DOMContentLoaded', () => {
    // 1. MECANISMO DE FALLBACK: Verifica se a biblioteca Leaflet carregou
    if (typeof L === 'undefined') {
        console.warn("Leaflet não carregou. Ativando fallback sem mapa.");
        document.getElementById('map').innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background: #fef2f2; color: #ef4444; padding: 20px; text-align: center; border-radius: 12px; border: 1px dashed #ef4444;">
                <i class="fa-solid fa-wifi" style="font-size: 3rem; margin-bottom: 15px;"></i>
                <h3>Mapa Indisponível</h3>
                <p style="font-size: 0.9rem; margin-top: 10px;">Não foi possível carregar a biblioteca de mapas (verifique sua conexão).<br>No entanto, a lista de rastreamento ao lado continua funcionando.</p>
            </div>
        `;
    } else {
        mapaDisponivel = true;
        // Inicializa o Mapa
        map = L.map('map').setView([-23.550520, -46.633308], 12);
        
        // Adiciona as "folhas" do mapa
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

    // 2. Carrega os dados iniciais (Sempre vai rodar, com ou sem mapa)
    carregarRastreamento();

    // 3. Atualização automática a cada 10 segundos (Polling)
    setInterval(carregarRastreamento, 10000);

    // 4. Eventos de Filtro
    const inputBusca = document.getElementById('filtro-busca');
    const selectStatus = document.getElementById('filtro-status');
    
    if(inputBusca) inputBusca.addEventListener('input', desenharMapaELista);
    if(selectStatus) selectStatus.addEventListener('change', desenharMapaELista);
});

async function carregarRastreamento() {
    try {
        const res = await fetch('api/rastreamento.php');
        if (!res.ok) throw new Error('Erro na resposta da API');
        
        todosPedidos = await res.json();
        desenharMapaELista();
    } catch (e) {
        console.error("Erro ao buscar rastreamento", e);
        
        // FALLBACK DA API: Se a API falhar, avisa na lista
        const listaContainer = document.getElementById('lista-entregas');
        if (listaContainer) {
            listaContainer.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #ef4444; font-size: 0.9rem;">
                    <i class="fa-solid fa-server" style="font-size: 2rem; margin-bottom: 10px;"></i><br>
                    Falha ao conectar com o servidor de rastreamento.
                </div>
            `;
        }
    }
}

function desenharMapaELista() {
    const busca = document.getElementById('filtro-busca').value.toLowerCase();
    const status = document.getElementById('filtro-status').value;

    // Filtra os pedidos
    const filtrados = todosPedidos.filter(p => {
        const matchBusca = p.codigo.toLowerCase().includes(busca) || 
                           p.cliente.toLowerCase().includes(busca) || 
                           (p.motorista_nome && p.motorista_nome.toLowerCase().includes(busca));
        
        let matchStatus = true;
        if (status !== 'Todos') {
            if (status === 'Pendente') matchStatus = (p.status === 'Pendente');
            else matchStatus = (p.status === status);
        }
        return matchBusca && matchStatus;
    });

    // Se o mapa estiver disponível, limpa os marcadores antigos
    if (mapaDisponivel && map) {
        markers.forEach(m => map.removeLayer(m));
        markers = [];
    }

    // Desenha a lista lateral (Sempre funciona)
    const listaContainer = document.getElementById('lista-entregas');
    listaContainer.innerHTML = '';

    if (filtrados.length === 0) {
        listaContainer.innerHTML = '<p style="padding:15px; color:#6b7280; text-align:center;">Nenhuma entrega encontrada.</p>';
    }

    filtrados.forEach(p => {
        // 1. Define a cor do pino baseada no status
        let cor = 'blue';
        if (p.status === 'Entregue') cor = 'green';
        if (p.status === 'Pendente') cor = 'red';
        if (p.status === 'Em Rota') cor = 'orange';

        // 2. Adiciona ao Mapa (Apenas se o mapa estiver carregado)
        if (mapaDisponivel && map) {
            const icon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div style='background-color:${cor}; width: 15px; height: 15px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5);'></div>`,
                iconSize: [15, 15],
                iconAnchor: [7, 7]
            });

            const marker = L.marker([p.lat, p.lng], { icon: icon }).addTo(map);
            marker.bindPopup(`
                <strong>${p.codigo}</strong><br>
                ${p.cliente}<br>
                Status: ${p.status}<br>
                Motorista: ${p.motorista_nome || 'Não atribuído'}
            `);
            markers.push(marker);
        }

        // 3. Adiciona na Lista Lateral
        listaContainer.innerHTML += `
            <div class="item-rastreamento">
                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                    <strong>${p.codigo}</strong>
                    <span style="color:${cor}; font-weight:bold;">${p.status}</span>
                </div>
                <div style="color:#6b7280; margin-bottom:5px;">${p.cliente} - ${p.endereco}</div>
                <div style="font-size:0.8rem; color:#9ca3af;">
                    <i class="fa-solid fa-truck"></i> ${p.veiculo_placa || 'S/ Veículo'} | 
                    <i class="fa-solid fa-user"></i> ${p.motorista_nome || 'S/ Motorista'}
                </div>
            </div>
        `;
    });

    // Ajusta o zoom do mapa para mostrar todos os pinos
    if (mapaDisponivel && map && markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}