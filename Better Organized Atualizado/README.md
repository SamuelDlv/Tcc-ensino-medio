# BetterOrganized — Versão Atualizada

Esta é a versão atualizada do **BetterOrganized**, projeto que desenvolvi a partir do TCC de 2023 para continuar trabalhando na ideia de gerenciamento dos computadores dos laboratórios.

O sistema permite acompanhar máquinas, baias, componentes de hardware, ocorrências técnicas e histórico de atendimento, com perfis diferentes para Professor e Suporte.

> Esta pasta faz parte do repositório `Tcc-ensino-medio` e não é um repositório Git separado.

## O que mudou nesta versão

A versão atualizada reorganiza a aplicação e melhora a estrutura em relação ao projeto original. O foco continua sendo o gerenciamento dos laboratórios, mas com uma base mais organizada para continuar evoluindo o sistema.

## Funcionalidades

- Consulta dos laboratórios e computadores
- Visualização das baias
- Cadastro e edição de máquinas
- Registro de componentes de hardware
- Abertura e acompanhamento de ocorrências
- Histórico de atendimentos
- Relatórios
- Geração de relatórios em PDF
- Perfis de Professor e Suporte

## Requisitos

- PHP 8.1+
- Extensão `mysqli`
- MySQL 8 ou MariaDB 10.4+
- Servidor web apontando para `public/`
- Navegador moderno

## Instalação rápida

Clone o repositório principal e entre nesta pasta:

```bash
git clone https://github.com/SamuelDlv/Tcc-ensino-medio.git
cd "Tcc-ensino-medio/Better Organized Atualizado"
```

Crie o banco:

```bash
mysql -u root -p < database/schema.sql
```

Depois configure as variáveis de ambiente a partir de `.env.example` e inicie o servidor:

```bash
php -S 127.0.0.1:8000 -t public
```

Acesse `http://127.0.0.1:8000`.

## Configuração

As configurações locais e credenciais devem ser mantidas fora do código. Use `.env.example` como modelo para criar o `.env` do ambiente local.

A pasta `config/` concentra a conexão com o banco, regras compartilhadas e geração interna de PDF. Consulte o README dessa pasta quando precisar entender essas partes da aplicação.

## Estrutura

```text
Better Organized Atualizado/
├── config/
├── database/
├── public/
├── .env.example
├── .gitignore
└── README.md
```

## Segurança

As credenciais locais não devem ser colocadas no código ou enviadas ao GitHub. As contas de demonstração e demais configurações presentes no projeto são destinadas ao ambiente acadêmico/local.

Antes de qualquer uso real, ainda é necessário revisar autenticação, armazenamento de senhas, HTTPS, permissões e controles contra tentativas repetidas de acesso.

## Autor

Projeto desenvolvido e mantido por **SamuelDlv**.

GitHub: https://github.com/SamuelDlv

Esta versão faz parte da evolução do meu TCC e foi mantida no mesmo repositório para facilitar a comparação com a versão original.
