# MorpheusERP

<p align="center">
  <img src="public/images/logo.png" width="150" alt="MorpheusERP Logo">
</p>

<p align="center">
  Sistema de gestão empresarial desenvolvido para o Empório Maxx
</p>

## Sobre o MorpheusERP

MorpheusERP é um sistema completo de gestão empresarial (ERP) desenvolvido especificamente para as necessidades do Empório Maxx. O sistema oferece funcionalidades para:

- Gestão de Produtos
- Gestão de Fornecedores
- Controle de Estoque
- Controle de Entradas e Saídas
- Gestão de Usuários
- Definição de Locais de Destino

## Tecnologias Utilizadas

- PHP 8.2+
- Laravel 12
- MySQL/MariaDB
- JavaScript
- HTML5/CSS3
- XAMPP (PHP, Apache, MySQL)

## Requisitos

- PHP 8.2 ou superior
- Servidor web (Apache/Nginx)
- MySQL 5.7+ ou MariaDB 10.3+
- Composer
- Node.js e NPM (para compilação de assets)
- XAMPP (recomendado para ambiente de desenvolvimento)

## Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/iury-hilario/MorpheusERP.git
cd MorpheusERP
```

### 2. Instale as dependências PHP

```bash
composer install
```

### 3. Instale as dependências JavaScript

```bash
npm install
```

### 4. Configure o ambiente

Copie o arquivo `.env.example` para `.env` e configure as variáveis de ambiente:

```bash
cp .env.example .env
```

Edite o arquivo `.env` para configurar:
- Conexão com o banco de dados
- Nome da aplicação
- Configurações de e-mail (se necessário)

### 5. Gere a chave da aplicação

```bash
php artisan key:generate
```

### 6. Crie o banco de dados

Crie um banco de dados MySQL/MariaDB e configure-o no arquivo `.env`.

Em seguida, execute as migrações e os seeders para criar as tabelas e preencher o banco de dados com dados iniciais:

```bash
php artisan migrate --seed
```

Alternativamente, você pode importar o esquema SQL manualmente:

```bash
mysql -u root -p nome_do_banco < database/migrations/morpheusERP_schema.sql
```

Ou use o phpMyAdmin do XAMPP para criar o banco de dados e importar o esquema.

### 7. Compile os assets

```bash
npm run build
```

### 8. Configure o servidor web

Se estiver usando o XAMPP:
1. Coloque o projeto na pasta `htdocs`
2. Acesse o projeto via `http://localhost/MorpheusERP/public`

Para desenvolvimento, você pode usar o servidor embutido do Laravel:

```bash
php artisan serve
```

## Estrutura do Projeto

- `app/` - Lógica principal da aplicação
- `database/` - Migrações e esquema do banco de dados
- `public/` - Arquivos públicos e ponto de entrada da aplicação
- `resources/` - Views, CSS, JavaScript
  - `css/` - Arquivos CSS organizados por módulo
  - `views/` - Templates Blade organizados por módulo
- `routes/` - Definições de rotas da aplicação
- `config/` - Arquivos de configuração

## Módulos Principais

### Módulo de Produtos
- Cadastro, edição e busca de produtos
- Gerenciamento de códigos e tipos
- Definição de preços de custo e venda

### Módulo de Fornecedores
- Cadastro e gestão de fornecedores
- Associação com produtos

### Módulo de Usuários
- Cadastro de usuários com níveis de acesso
- Controle de permissões

### Entrada e Saída de Produtos
- Registro de movimentações
- Rastreamento de estoque

## Contribuição

Para contribuir com o desenvolvimento do MorpheusERP:

1. Faça um fork do repositório
2. Crie um branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova funcionalidade'`)
4. Push para o branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## Segurança

Se você descobrir alguma vulnerabilidade de segurança no MorpheusERP, por favor envie um e-mail para [rpmorfeus@gmail.com](mailto:seu-email@exemplo.com).

## Licença

O MorpheusERP é um software proprietário. Todos os direitos reservados ao Empório Maxx.
