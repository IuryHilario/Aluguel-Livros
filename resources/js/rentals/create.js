document.addEventListener('DOMContentLoaded', function() {
    // Elementos do formulário
    const idUsuarioInput = document.getElementById('id_usuario');
    const usuarioNomeInput = document.getElementById('usuario_nome');
    const idLivroInput = document.getElementById('id_livro');
    const livroTituloInput = document.getElementById('livro_titulo');
    const dtAluguelInput = document.getElementById('dt_aluguel');
    const dtDevolucaoInput = document.getElementById('dt_devolucao');
    const previewContainer = document.getElementById('preview-container');
    
    // Elementos do Modal Usuário
    const usuarioModal = document.getElementById('usuarioModal');
    const buscarUsuarioBtn = document.getElementById('buscarUsuarioBtn');
    const fecharModalUsuario = document.getElementById('fecharModalUsuario');
    const searchUsuarioInput = document.getElementById('searchUsuario');
    const btnSearchUsuario = document.getElementById('btnSearchUsuario');
    const usuariosSearchResults = document.getElementById('usuariosSearchResults');
    
    // Elementos do Modal Livro
    const livroModal = document.getElementById('livroModal');
    const buscarLivroBtn = document.getElementById('buscarLivroBtn');
    const fecharModalLivro = document.getElementById('fecharModalLivro');
    const searchLivroInput = document.getElementById('searchLivro');
    const btnSearchLivro = document.getElementById('btnSearchLivro');
    const livrosSearchResults = document.getElementById('livrosSearchResults');
    
    // Verificar se foi passado um livro na URL
    const urlParams = new URLSearchParams(window.location.search);
    const bookId = urlParams.get('book_id');
    
    if (bookId) {
        // Buscar informações do livro e preencher automaticamente
        fetch(`/rentals/search/books?id=${bookId}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const livro = data[0];
                    idLivroInput.value = livro.id_livro;
                    livroTituloInput.value = livro.titulo;
                    livroTituloInput.dataset.autor = livro.autor;
                    updatePreview();
                }
            })
            .catch(error => {
                console.error('Erro ao buscar livro:', error);
            });
    }
    
    // Função para atualizar o preview
    function updatePreview() {
        if (idUsuarioInput.value && idLivroInput.value && dtAluguelInput.value && dtDevolucaoInput.value) {
            // Atualizar os campos de preview
            document.getElementById('preview-usuario').textContent = usuarioNomeInput.value;
            document.getElementById('preview-livro').textContent = livroTituloInput.value;
            document.getElementById('preview-autor').textContent = livroTituloInput.dataset.autor || '';
            
            // Calcular período
            const dataInicio = new Date(dtAluguelInput.value);
            const dataFim = new Date(dtDevolucaoInput.value);
            const diffTime = Math.abs(dataFim - dataInicio);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            document.getElementById('preview-periodo').textContent = `${diffDays} dias (${formatarData(dataInicio)} - ${formatarData(dataFim)})`;
            
            // Mostrar o container de preview
            previewContainer.style.display = 'block';
        } else {
            previewContainer.style.display = 'none';
        }
    }
    
    // Formatar data
    function formatarData(data) {
        const dia = String(data.getDate()).padStart(2, '0');
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const ano = data.getFullYear();
        return `${dia}/${mes}/${ano}`;
    }
    
    // Event listeners para o modal de usuário
    buscarUsuarioBtn.addEventListener('click', () => {
        usuarioModal.style.display = 'block';
        searchUsuarioInput.focus();
        // Carregar todos os usuários inicialmente
        buscarUsuarios('');
    });
    
    fecharModalUsuario.addEventListener('click', () => {
        usuarioModal.style.display = 'none';
    });
    
    // Event listeners para o modal de livro
    buscarLivroBtn.addEventListener('click', () => {
        livroModal.style.display = 'block';
        searchLivroInput.focus();
        // Carregar todos os livros inicialmente
        buscarLivros('');
    });
    
    fecharModalLivro.addEventListener('click', () => {
        livroModal.style.display = 'none';
    });
    
    // Fechar o modal ao clicar fora dele
    window.addEventListener('click', (event) => {
        if (event.target === usuarioModal) {
            usuarioModal.style.display = 'none';
        }
        if (event.target === livroModal) {
            livroModal.style.display = 'none';
        }
    });
    
    // Buscar usuários com input de texto
    searchUsuarioInput.addEventListener('input', function() {
        buscarUsuarios(this.value.trim());
    });
    
    // Botão para buscar usuários quando clicar no botão
    btnSearchUsuario.addEventListener('click', function() {
        buscarUsuarios(searchUsuarioInput.value.trim());
    });
    
    // Enter para buscar usuários
    searchUsuarioInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            buscarUsuarios(this.value.trim());
        }
    });
    
    // Função para buscar usuários
    function buscarUsuarios(searchTerm) {
        usuariosSearchResults.innerHTML = '<div class="loading">Buscando usuários...</div>';
        
        fetch(`/rentals/search/users?term=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                usuariosSearchResults.innerHTML = '';
                
                if (data.length === 0) {
                    usuariosSearchResults.innerHTML = '<div class="no-results">Nenhum usuário encontrado</div>';
                    return;
                }
                
                data.forEach(usuario => {
                    const usuarioElement = document.createElement('div');
                    usuarioElement.className = 'search-result-item';
                    usuarioElement.innerHTML = `
                        <div class="search-result-title">${usuario.nome}</div>
                        <div class="search-result-subtitle">${usuario.email}</div>
                    `;
                    
                    usuarioElement.addEventListener('click', () => {
                        idUsuarioInput.value = usuario.id_usuario;
                        usuarioNomeInput.value = usuario.nome;
                        usuarioModal.style.display = 'none';
                        updatePreview();
                    });
                    
                    usuariosSearchResults.appendChild(usuarioElement);
                });
            })
            .catch(error => {
                usuariosSearchResults.innerHTML = '<div class="error">Erro ao buscar usuários</div>';
                console.error('Erro na busca de usuários:', error);
            });
    }
    
    // Buscar livros com input de texto
    searchLivroInput.addEventListener('input', function() {
        buscarLivros(this.value.trim());
    });
    
    // Botão para buscar livros
    btnSearchLivro.addEventListener('click', function() {
        buscarLivros(searchLivroInput.value.trim());
    });
    
    // Enter para buscar livros
    searchLivroInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            buscarLivros(this.value.trim());
        }
    });
    
    // Função para buscar livros
    function buscarLivros(searchTerm) {
        livrosSearchResults.innerHTML = '<div class="loading">Buscando livros disponíveis...</div>';
        
        fetch(`/rentals/search/books?term=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                livrosSearchResults.innerHTML = '';
                
                if (data.length === 0) {
                    livrosSearchResults.innerHTML = '<div class="no-results">Nenhum livro encontrado</div>';
                    return;
                }
                
                data.forEach(livro => {
                    const livroElement = document.createElement('div');
                    livroElement.className = 'search-result-item';
                    livroElement.innerHTML = `
                        <div class="search-result-title">${livro.titulo}</div>
                        <div class="search-result-subtitle">Autor: ${livro.autor} | Disponíveis: ${livro.quantidade}</div>
                    `;
                    
                    livroElement.addEventListener('click', () => {
                        idLivroInput.value = livro.id_livro;
                        livroTituloInput.value = livro.titulo;
                        livroTituloInput.dataset.autor = livro.autor;
                        livroModal.style.display = 'none';
                        updatePreview();
                    });
                    
                    livrosSearchResults.appendChild(livroElement);
                });
            })
            .catch(error => {
                livrosSearchResults.innerHTML = '<div class="error">Erro ao buscar livros</div>';
                console.error('Erro na busca de livros:', error);
            });
    }
    
    // Event listeners para atualizar o preview
    idUsuarioInput.addEventListener('change', updatePreview);
    idLivroInput.addEventListener('change', updatePreview);
    dtAluguelInput.addEventListener('change', updatePreview);
    dtDevolucaoInput.addEventListener('change', updatePreview);
});