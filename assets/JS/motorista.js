// assets/js/motorista.js

function abrirModalPOD(id, codigo) {
    document.getElementById('pod-id').value = id;
    document.getElementById('pod-codigo').textContent = 'Pedido: ' + codigo;
    
    // RF07: Captura automática de geolocalização
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById('form-pod').dataset.lat = position.coords.latitude;
                document.getElementById('form-pod').dataset.lng = position.coords.longitude;
            },
            (error) => console.warn('Geolocalização negada ou com erro.')
        );
    }
    
    document.getElementById('modal-pod').style.display = 'flex';
}

function fecharModalPOD() {
    document.getElementById('modal-pod').style.display = 'none';
}

document.getElementById('form-pod')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    
    btn.disabled = true;
    btn.innerHTML = 'Enviando...';

    const formData = new FormData(form);
    // Adiciona lat e lng capturados
    formData.append('latitude', form.dataset.lat || 0);
    formData.append('longitude', form.dataset.lng || 0);

    try {
        const res = await fetch('api/pod.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if(data.sucesso) {
            alert(data.mensagem);
            window.location.reload();
        } else {
            alert(data.erro || 'Erro ao confirmar');
            btn.disabled = false;
            btn.innerHTML = 'Confirmar';
        }
    } catch (err) {
        alert('Erro de rede');
        btn.disabled = false;
    }
});