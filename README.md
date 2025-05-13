# Aluga Livros

## Descrição do Sistema

O Aluga Livros é uma plataforma web para gerenciamento de aluguel de livros. O sistema permite que usuários consultem o acervo disponível, realizem reservas e aluguéis de livros, além de oferecer funcionalidades de administração para controle do acervo e dos empréstimos.

## Funcionalidades Principais

O sistema é administrado exclusivamente por um Administrador que gerencia todos os aspectos da biblioteca, similar a um sistema de biblioteca pública:

- Cadastro, edição e remoção de livros no acervo
- Cadastro e gerenciamento de usuários (leitores da biblioteca)
- Registro e controle de aluguéis de livros
- Gerenciamento de devoluções e renovações
- Busca de livros por título, autor, categoria ou disponibilidade
- Visualização detalhada de informações dos livros
- Geração de relatórios de aluguéis e devoluções
- Controle de pagamentos e multas por atraso

## Instruções de Instalação e Execução

### Pré-requisitos

- PHP 8.0 ou superior
- Composer
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Node.js e NPM (para compilação de assets)

### Passos para Instalação

1. Clone o repositório:

   ```bash
   git clone https://github.com/IuryHilario/Aluguel-Livros.git
   cd Aluguel-Livros
   ```

2. Instale as dependências do PHP:

   ```bash
   composer install
   ```

3. Instale as dependências do JavaScript:

   ```bash
   npm install
   npm run dev
   ```

4. Configure o ambiente:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure o banco de dados no arquivo `.env` com suas credenciais:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=aluga_livros
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Execute as migrações e seeders:

   ```bash
   php artisan migrate --seed
   ```

7. Inicie o servidor de desenvolvimento:

   ```bash
   php artisan serve
   ```

8. Acesse o sistema em: `http://localhost:8000`

### Acesso ao Sistema

- **Usuário Administrador**:
  - Email: `admin@alugalivros.com`
  - Senha: admin123

## Tecnologias Utilizadas

- **Backend**: Laravel Framework (PHP)
- **Frontend**: HTML, CSS, JavaScript, Bootstrap
- **Banco de Dados**: MySQL
- **Autenticação**: Laravel Breeze/Sanctum
- **Ambiente de Desenvolvimento**: XAMPP (Apache, MySQL, PHP)
- **Outras ferramentas**: Composer, NPM

## Estrutura do Projeto

O projeto segue a estrutura padrão do Laravel, com os seguintes diretórios principais:

- `app/` - Contém o código principal da aplicação
- `config/` - Arquivos de configuração
- `database/` - Migrações e seeders
- `public/` - Arquivos públicos acessíveis pela web
- `resources/` - Assets, views, linguagens
- `routes/` - Definição de rotas

## Contribuição

Para contribuir com o projeto:

1. Faça um fork do repositório
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Faça commit das suas alterações (`git commit -m 'Adiciona nova funcionalidade'`)
4. Envie para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

## Créditos

Desenvolvido por Iury Hilário. Para suporte ou contato, envie um email para [iuryhilario.dev@gmail.com].

