const sizeInput = document.getElementById('sizeInput');
const inputsContainer = document.getElementById('inputsContainer');
const errorMsg = document.getElementById('errorMsg');

sizeInput.addEventListener('input', () => {
    sizeInput.value = sizeInput.value.replace(/[^0-9]/g, '');
});

function soloEnteros(input) {
    input.addEventListener('input', () => {

        input.value = input.value.replace(/[^0-9]/g, '');
    });
}

function generarCasillas(valores = null) {
    errorMsg.textContent = '';
    const n = parseInt(sizeInput.value, 10);

    if (isNaN(n) || n < 1 || n > 200) {
        errorMsg.textContent = 'Ingrese un tamaño válido entre 1 y 200.';
        return;
    }

    inputsContainer.innerHTML = '';
    for (let i = 0; i < n; i++) {
        const div = document.createElement('div');
        div.className = 'casilla'; 
        
        
        div.innerHTML = `<label>Pos[${i}]:</label> <input type="text" class="vec-val" maxlength="4" value="${valores ? valores[i] : ''}">`;
        
        soloEnteros(div.querySelector('input'));
        inputsContainer.appendChild(div);
    }
}

function cargarEjemplo() {
    const ejemplo = [89, 10, 7, 29, 13, 5, 14, 3, 20, 2];
    sizeInput.value = ejemplo.length;
    generarCasillas(ejemplo);
    procesar();
}

async function procesar() {
    errorMsg.textContent = '';
    const inputs = document.querySelectorAll('.vec-val');
    if (inputs.length === 0) {
        errorMsg.textContent = 'Primero genere las casillas.';
        return;
    }

    const vector = [];
    for (let i = 0; i < inputs.length; i++) {
        const v = inputs[i].value.trim();
        if (v === '') {
            errorMsg.textContent = `Falta un número válido en la Pos[${i}].`;
            return;
        }
        vector.push(parseInt(v, 10));
    }

    try {
        const resp = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ vector })
        });
        const data = await resp.json();

        if (data.error) {
            errorMsg.textContent = data.error;
            return;
        }

        document.getElementById('results').classList.remove('oculto');
        document.getElementById('vecOriginal').textContent = data.original;
        document.getElementById('res1').textContent = `${data.p1_count} encontrados -> ${data.p1_detail}`;
        document.getElementById('res2').textContent = `${data.p2_count} (Múltiplos de 3, 4, 5 o 7: ${data.p2_detail})`;
        document.getElementById('vecOrdenado').textContent = data.ordenado;
        document.getElementById('resProm').textContent = data.promedio;
        document.getElementById('resMayores').textContent = data.mayores;
        document.getElementById('resMenores').textContent = data.menores;
    } catch (e) {
        
        errorMsg.textContent = 'Error conectando con api.php. Verifica que Laragon esté iniciado.';
    }
}

window.onload = generarCasillas;