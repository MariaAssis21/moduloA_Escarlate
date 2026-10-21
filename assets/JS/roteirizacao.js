// assets/js/roteirizacao.js
let map;
let routeLayers = [];

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-rota');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const btn = form.querySelector('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Calculando...';
        btn.disabled = true;

        const formData = new FormData(form);

        try {
            const res = await fetch('api/ia_rotas.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.sucesso) {
                document.getElementById('resultado-rota').style.display = 'block';
                document.getElementById('info-distancia').innerHTML = `${data.distancia_total_km} km`;
                document.getElementById('info-custo').innerHTML = `R$ ${data.custo_estimado}`;
                document.getElementById('info-entregas').innerHTML = data.total_entregas;

                desenharMapa(data.rota);
                preencherLista(data.rota);
            } else {
                alert(data.erro || 'Erro ao gerar rota');
            }
        } catch (error) {
            console.error(error);
            alert('Erro na comunicação com a IA');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
});

function desenharMapa(pontos) {
    if (!map) {
        map = L.map('map').setView([-23.550520, -46.633308], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

    // Limpa a rota anterior
    routeLayers.forEach(layer => map.removeLayer(layer));
    routeLayers = [];

    const latlngs = [];

    pontos.forEach((p, index) => {
        latlngs.push([p.lat, p.lng]);

        // Cria um ícone numerado baseado na ordem da rota
        const icon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color: ${p.tipo === 'origem' ? '#2563eb' : '#10b981'}; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">${index === 0 ? 'B' : index}</div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });

        const marker = L.marker([p.lat, p.lng], { icon: icon }).addTo(map);
        marker.bindPopup(`<strong>${p.nome}</strong>`);
        routeLayers.push(marker);
    });

    // Desenha a linha conectando os pontos
    const polyline = L.polyline(latlngs, { color: '#8b5cf6', weight: 4, dashArray: '5, 10' }).addTo(map);
    routeLayers.push(polyline);

    // Dá zoom para caber todos os pontos
    map.fitBounds(polyline.getBounds().pad(0.1));
}

function preencherLista(pontos) {
    const lista = document.getElementById('lista-passos');
    lista.innerHTML = pontos.map((p, index) => `
        <li>
            <div class="step-icon" style="background-color: ${p.tipo === 'origem' ? '#2563eb' : '#10b981'};">
                ${index === 0 ? 'B' : index}
            </div>
            <div>
                <strong style="color: #1f2937;">${p.nome}</strong><br>
                ${index > 0 ? `<small style="color: #6b7280;">Distância do ponto anterior: ${p.distancia_anterior} km</small>` : '<small style="color: #6b7280;">Ponto de Partida</small>'}
            </div>
        </li>
    `).join('');
}