// Script para pesquisa global
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('global-search');
    const searchResultsDropdown = document.getElementById('search-results-dropdown');
    const searchResultsContent = document.getElementById('search-results-content');
    let searchTimeout;

    // Verificação de elementos essenciais
    if (!searchInput || !searchResultsDropdown || !searchResultsContent) {
        console.error('Elementos de pesquisa não encontrados');
        return;
    }

    // Detectar quando o usuário digita na pesquisa
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            searchResultsDropdown.style.display = 'none';
            return;
        }

        // Exibir loading
        searchResultsDropdown.style.display = 'block';
        searchResultsContent.innerHTML = `
            <div class="search-loading">
                <i class="fas fa-spinner"></i>
                <p>Buscando resultados...</p>
            </div>
        `;

        // Delay para reduzir o número de requisições
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    // Focar no input mostra os resultados anteriores se existirem
    searchInput.addEventListener('focus', function() {
        const query = this.value.trim();
        if (query.length >= 2 && searchResultsContent.children.length > 0 && 
            !searchResultsContent.querySelector('.search-loading')) {
            searchResultsDropdown.style.display = 'block';
        }
    });

    // Fechar dropdown quando clicar fora
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResultsDropdown.contains(e.target)) {
            searchResultsDropdown.style.display = 'none';
        }
    });

    // Impedir que cliques dentro do dropdown fechem o dropdown
    searchResultsDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Função para realizar a pesquisa via AJAX
    function performSearch(query) {
        fetch(`/api/search?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Falha na requisição');
                }
                return response.json();
            })
            .then(data => {
                displayResults(data, query);
            })
            .catch(error => {
                console.error('Erro na pesquisa:', error);
                searchResultsContent.innerHTML = `
                    <div class="search-no-results">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Erro ao realizar a pesquisa. Tente novamente.</p>
                    </div>
                `;
            });
    }

    // Função para exibir os resultados
    function displayResults(results, query) {
        if ((!results.livros || !results.livros.length) && 
            (!results.usuarios || !results.usuarios.length)) {
            searchResultsContent.innerHTML = `
                <div class="search-no-results">
                    <i class="fas fa-search"></i>
                    <p>Nenhum resultado encontrado para "${query}"</p>
                </div>
            `;
            return;
        }

        let html = '';

        // Adiciona resultados de livros
        if (results.livros && results.livros.length > 0) {
            html += `
                <div class="search-category">
                    <div class="search-category-title">Livros</div>
            `;

            results.livros.forEach(livro => {
                const disponibilidade = livro.disponivel ? 
                    '<span style="color: green; font-size: 0.9rem;"><i class="fas fa-check-circle"></i> Disponível</span>' : 
                    '<span style="color: red; font-size: 0.9rem;"><i class="fas fa-times-circle"></i> Indisponível</span>';
                const tituloHighlighted = highlightMatch(livro.titulo, query);
                // Separar título e subtítulo se houver (exemplo: "Harry Potter e a Pedra Filosofal Vol 7")
                let titulo = tituloHighlighted;
                let subtitulo = '';
                if (tituloHighlighted.includes(':')) {
                    [titulo, subtitulo] = tituloHighlighted.split(/:(.+)/).map(s => s.trim());
                } else if (tituloHighlighted.match(/(.+)(Vol|vol|Volume|volume)\s*\d+/)) {
                    const match = tituloHighlighted.match(/(.+?)(\s*(Vol|vol|Volume|volume)\s*\d+.*)/);
                    if (match) {
                        titulo = match[1].trim();
                        subtitulo = match[2].trim();
                    }
                }
                html += `
                    <div class="search-result-item" data-href="/books/${livro.id}">
                        <div class="search-result-icon">
                            <i class="fas fa-book search-book-icon"></i>
                        </div>
                        <div class="search-result-content">
                            <div class="search-result-title">
                                <div>${titulo}</div>
                                ${subtitulo ? `<div style='font-size:0.95em;color:#888;'>${subtitulo}</div>` : ''}
                            </div>
                            <div style="margin: 4px 0 2px 0;">${disponibilidade}</div>
                            <div class="search-result-subtitle">Autor: ${livro.autor}</div>
                        </div>
                    </div>
                `;
            });

            html += `</div>`;
        }

        // Adiciona resultados de usuários
        if (results.usuarios && results.usuarios.length > 0) {
            html += `
                <div class="search-category">
                    <div class="search-category-title">Usuários</div>
            `;

            results.usuarios.forEach(usuario => {
                const nomeHighlighted = highlightMatch(usuario.nome, query);
                
                html += `
                    <div class="search-result-item" data-href="/users/${usuario.id}">
                        <div class="search-result-icon">
                            <i class="fas fa-user search-user-icon"></i>
                        </div>
                        <div class="search-result-content">
                            <div class="search-result-title">${nomeHighlighted}</div>
                            <div class="search-result-subtitle">${usuario.email}</div>
                        </div>
                    </div>
                `;
            });

            html += `</div>`;
        }

        searchResultsContent.innerHTML = html;
        
        // Adicionar manipuladores de eventos para os itens de resultado
        document.querySelectorAll('.search-result-item').forEach(item => {
            item.addEventListener('click', function() {
                const href = this.getAttribute('data-href');
                if (href) {
                    window.location.href = href;
                }
            });
        });
    }

    // Função para destacar o texto pesquisado
    function highlightMatch(text, query) {
        if (!query || !text) return text || '';
        try {
            const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
            return text.replace(regex, '<span class="search-highlight">$1</span>');
        } catch (e) {
            console.error('Erro ao destacar texto:', e);
            return text;
        }
    }

    // Função para escapar caracteres especiais em regex
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
});