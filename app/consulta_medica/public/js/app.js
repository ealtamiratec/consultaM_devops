/**
 * JavaScript Principal - Sistema de Consulta Médica
 * Sistema de Atención Médica - Consulta Externa
 */

// Configuración global
const App = {
    baseUrl: window.location.origin,
    
    // Inicializar aplicación
    init() {
        this.initAlerts();
        this.initForms();
        this.initConfirmations();
        this.initAutocomplete();
        this.initMedicosByEspecialidad();
    },

    // Auto-ocultar alertas
    initAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    },

    // Validación de formularios
    initForms() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });

        // Limpiar errores al escribir
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', () => {
                input.classList.remove('is-invalid');
                const feedback = input.parentElement.querySelector('.invalid-feedback');
                if (feedback) feedback.remove();
            });
        });
    },

    // Validar formulario
    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                this.showFieldError(field, 'Este campo es obligatorio');
            }
        });

        // Validar email
        const emailFields = form.querySelectorAll('[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                isValid = false;
                field.classList.add('is-invalid');
                this.showFieldError(field, 'Ingrese un email válido');
            }
        });

        return isValid;
    },

    // Mostrar error en campo
    showFieldError(field, message) {
        const existingError = field.parentElement.querySelector('.invalid-feedback');
        if (existingError) existingError.remove();

        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentElement.appendChild(errorDiv);
    },

    // Validar email
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    // Confirmaciones de eliminación
    initConfirmations() {
        document.querySelectorAll('[data-confirm]').forEach(element => {
            element.addEventListener('click', (e) => {
                const message = element.dataset.confirm || '¿Está seguro de realizar esta acción?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    },

    // Autocomplete para búsqueda de pacientes
    initAutocomplete() {
        const autocompleteInputs = document.querySelectorAll('[data-autocomplete]');
        
        autocompleteInputs.forEach(input => {
            const container = input.closest('.autocomplete-container');
            const resultsDiv = container.querySelector('.autocomplete-results');
            const hiddenInput = container.querySelector('input[type="hidden"]');
            const endpoint = input.dataset.autocomplete;

            let debounceTimer;

            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                const query = input.value.trim();

                if (query.length < 2) {
                    resultsDiv.classList.remove('show');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    this.fetchAutocomplete(endpoint, query, resultsDiv, hiddenInput, input);
                }, 300);
            });

            // Cerrar al hacer clic fuera
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    resultsDiv.classList.remove('show');
                }
            });
        });
    },

    // Fetch para autocomplete
    async fetchAutocomplete(endpoint, query, resultsDiv, hiddenInput, input) {
        try {
            const response = await fetch(`${this.baseUrl}/${endpoint}?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            resultsDiv.innerHTML = '';

            if (data.data && data.data.length > 0) {
                data.data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    div.innerHTML = `
                        <div class="autocomplete-item-title">${item.nombre_completo}</div>
                        <div class="autocomplete-item-subtitle">${item.numero_historia} - ${item.documento}</div>
                    `;
                    div.addEventListener('click', () => {
                        input.value = item.nombre_completo;
                        hiddenInput.value = item.id;
                        resultsDiv.classList.remove('show');
                        
                        // Disparar evento personalizado
                        input.dispatchEvent(new CustomEvent('autocomplete:select', { detail: item }));
                    });
                    resultsDiv.appendChild(div);
                });
                resultsDiv.classList.add('show');
            } else {
                resultsDiv.innerHTML = '<div class="autocomplete-item text-muted">No se encontraron resultados</div>';
                resultsDiv.classList.add('show');
            }
        } catch (error) {
            console.error('Error en autocomplete:', error);
        }
    },

    // Cargar médicos por especialidad
    initMedicosByEspecialidad() {
        const especialidadSelect = document.getElementById('especialidad_id');
        const medicoSelect = document.getElementById('medico_id');

        if (!especialidadSelect || !medicoSelect) return;

        especialidadSelect.addEventListener('change', async () => {
            const especialidadId = especialidadSelect.value;
            
            if (!especialidadId) {
                medicoSelect.innerHTML = '<option value="">Seleccione un médico</option>';
                return;
            }

            try {
                const response = await fetch(`${this.baseUrl}/api/medicos/especialidad/${especialidadId}`);
                const data = await response.json();

                medicoSelect.innerHTML = '<option value="">Seleccione un médico</option>';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(medico => {
                        const option = document.createElement('option');
                        option.value = medico.id;
                        option.textContent = `${medico.nombre_completo} (${medico.colegiatura})`;
                        medicoSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar médicos:', error);
            }
        });
    },

    // Calcular IMC
    calcularIMC(peso, talla) {
        if (!peso || !talla || talla <= 0) return 0;
        const tallaMetros = talla / 100;
        return (peso / (tallaMetros * tallaMetros)).toFixed(2);
    },

    // Formatear fecha
    formatDate(date) {
        const d = new Date(date);
        return d.toLocaleDateString('es-PE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    },

    // Mostrar notificación
    showNotification(message, type = 'info') {
        const container = document.querySelector('.page-content') || document.body;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} fade-in`;
        alert.innerHTML = `<span>${message}</span>`;
        container.insertBefore(alert, container.firstChild);

        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    App.init();
});

// Calcular IMC automáticamente
document.addEventListener('DOMContentLoaded', () => {
    const pesoInput = document.getElementById('peso');
    const tallaInput = document.getElementById('talla');
    const imcInput = document.getElementById('imc');

    if (pesoInput && tallaInput && imcInput) {
        const calcularIMC = () => {
            const peso = parseFloat(pesoInput.value) || 0;
            const talla = parseFloat(tallaInput.value) || 0;
            
            if (peso > 0 && talla > 0) {
                const imc = App.calcularIMC(peso, talla);
                imcInput.value = imc;
            }
        };

        pesoInput.addEventListener('input', calcularIMC);
        tallaInput.addEventListener('input', calcularIMC);
    }
});

// Toggle sidebar en móvil
document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }
});
