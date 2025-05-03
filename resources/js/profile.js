/**
 * Scripts para a página de perfil do usuário
 */

document.addEventListener('DOMContentLoaded', function() {
    // Gerenciamento de abas
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.getAttribute('data-tab');
            
            // Desativa todas as abas
            tabButtons.forEach(tab => tab.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Ativa a aba selecionada
            button.classList.add('active');
            document.getElementById(`${tabName}-content`).classList.add('active');
        });
    });
    
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const input = toggle.parentElement.querySelector('input');
            const icon = toggle.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Verificar se há uma mensagem de status na URL (flash message)
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    
    if (status === 'profile-updated') {
        showAlert('Perfil atualizado com sucesso!', 'success');
    }
    
    // Função para mostrar alertas na página
    function showAlert(message, type = 'info') {
        const alertContainer = document.createElement('div');
        alertContainer.className = `alert alert-${type}`;
        alertContainer.innerHTML = `
            <div class="alert-content">
                <i class="${getAlertIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="alert-close">&times;</button>
        `;
        
        document.querySelector('.profile-container').prepend(alertContainer);
        
        alertContainer.querySelector('.alert-close').addEventListener('click', function() {
            alertContainer.remove();
        });
        
        setTimeout(() => {
            if (alertContainer.parentNode) {
                alertContainer.remove();
            }
        }, 5000);
    }
    
    // Helper para pegar o ícone correto para cada tipo de alerta
    function getAlertIcon(type) {
        switch(type) {
            case 'success':
                return 'fas fa-check-circle';
            case 'error':
                return 'fas fa-exclamation-circle';
            default:
                return 'fas fa-info-circle';
        }
    }
    
    // Validação de formulário
    const form = document.querySelector('.profile-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const passwordConfirmation = document.getElementById('password_confirmation');
            
            if (password.value && password.value !== passwordConfirmation.value) {
                e.preventDefault();
                showAlert('As senhas não correspondem. Por favor, verifique.', 'error');
                
                tabButtons.forEach(tab => tab.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                const securityTab = document.querySelector('[data-tab="security"]');
                securityTab.classList.add('active');
                document.getElementById('security-content').classList.add('active');
            }
        });
    }
});