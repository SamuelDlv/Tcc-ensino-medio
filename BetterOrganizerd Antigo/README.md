# Better Organized — Versão Original

Sistema web acadêmico desenvolvido como Trabalho de Conclusão de Curso do ensino médio em 2023. O projeto organiza ocorrências de equipamentos em laboratórios, permitindo registrar problemas, identificar a baia afetada e indicar se o equipamento está com defeito ou em manutenção.

> **Sobre este diretório:** esta é a versão original do projeto e faz parte do repositório [`Tcc-ensino-medio`](https://github.com/SamuelDlv/Tcc-ensino-medio). Ela não é um repositório Git independente. Para obter o projeto, clone o repositório principal e entre nesta pasta.

```bash
git clone https://github.com/SamuelDlv/Tcc-ensino-medio.git
cd "Tcc-ensino-medio/BetterOrganizerd Antigo"
```

## Estado do projeto

Esta versão foi preservada como registro do projeto desenvolvido em 2023. A aplicação continua sendo PHP procedural com MySQL/MariaDB e não exige framework ou gerenciador de dependências.

## Requisitos

- PHP 7.4 ou superior, com as extensões `mysqli` e `session` habilitadas;
- MySQL 8 ou MariaDB 10.4 ou superior;
- Um servidor web local, como Apache, Nginx ou o servidor embutido do PHP;
- Git, caso o projeto seja obtido por clone do repositório principal.

## Instalação rápida

### 1. Obter o projeto

```bash
git clone https://github.com/SamuelDlv/Tcc-ensino-medio.git
cd "Tcc-ensino-medio/BetterOrganizerd Antigo"
```

### 2. Configurar o ambiente

Copie o modelo de configuração e edite os valores necessários:

```bash
cp .env.example .env
```

O sistema não carrega o arquivo `.env` automaticamente. Portanto, antes de iniciar o servidor, exporte as variáveis no terminal ou configure-as no Apache/Nginx. Em Linux/macOS, um exemplo é:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=better_organized
export DB_USER=root
export DB_PASSWORD='sua_senha_local'
```

No Windows, configure as mesmas variáveis no ambiente do sistema ou no painel do servidor web.

### 3. Criar o banco de dados

O script `database/schema.sql` recria o banco inteiro. Ele contém `DROP DATABASE IF EXISTS`, portanto **apaga os dados atuais do banco `better_organized`**. Execute-o apenas quando essa perda for desejada.

No MySQL Workbench:

1. Abra `database/schema.sql`.
2. Confira se está conectado ao servidor local correto.
3. Execute o script completo.
4. Verifique se o schema `better_organized` e as tabelas `Usuarios` e `Ocorrencias` foram criados.

Pelo terminal:

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p < database/schema.sql
```

### 4. Executar localmente

A partir desta pasta, inicie o servidor embutido do PHP apontando para `app/`:

```bash
php -S 127.0.0.1:8000 -t app
```

Depois acesse `http://127.0.0.1:8000` no navegador.

Também é possível configurar o Apache para usar `app/` como DocumentRoot. Não publique a raiz do repositório como DocumentRoot, porque ela contém documentação e arquivos de configuração destinados ao desenvolvimento.

## Primeiro acesso

O cadastro está disponível na tela inicial. O ID do usuário é gerado automaticamente pelo banco e não precisa ser informado. Crie um usuário com função `1` para professor ou `0` para suporte. Em um ambiente real, as senhas devem ser armazenadas com `password_hash()` e verificadas com `password_verify()`; o código legado ainda usa comparação direta e essa migração deve ser feita antes de qualquer uso com dados reais.

## Estrutura

```text
BetterOrganizerd Antigo/
├── app/
│   ├── config/              # Conexão centralizada
│   ├── LoginCadastro/       # Estilos e scripts da autenticação
│   ├── RotaProfessor/       # Fluxo do professor
│   ├── RotaSuporte/         # Fluxo do suporte
│   └── index.php            # Login e cadastro
├── database/
│   └── schema.sql           # Estrutura do banco
├── docs/
│   ├── ARCHITECTURE.md      # Decisões de organização
│   └── SECURITY.md          # Limitações e recomendações de segurança
├── .env.example
├── .gitignore
└── README.md
```

## Estados dos equipamentos

A tabela `Ocorrencias.status_ocorrencia` usa os seguintes valores:

| Valor | Estado | Exibição |
|---:|---|---|
| 0 | Funcionando | Cor padrão |
| 1 | Com defeito | Vermelho |
| 2 | Em manutenção | Amarelo |

## Gerar PDF

Na tela de detalhes de uma ocorrência, o botão vermelho **Gerar PDF** abre um PDF com o título, data, laboratório, baia, situação e descrição do registro. A geração é feita pelo endpoint `app/RotaSuporte/Laboratorio1/Detalhes/gerar_pdf.php`, que usa uma consulta preparada e exige uma sessão autenticada. O recurso não depende de Dompdf, mPDF, TCPDF ou FPDF.

## Limitações conhecidas

O sistema original foi criado como projeto acadêmico e mantém uma arquitetura procedural. Esta versão é preservada principalmente como registro histórico do TCC. Recomenda-se fazer a migração de senhas, adicionar proteção CSRF e separar autenticação, regras de negócio e apresentação antes de usar a aplicação fora de um ambiente de estudo.

## Relação com a versão atualizada

A versão atualizada do BetterOrganized está em `../Better Organized Atualizado/`. As duas versões permanecem neste mesmo repositório para preservar a evolução do projeto.

## Licença

Este repositório não define uma licença por padrão. Antes de permitir reutilização pública, escolha uma licença adequada ao trabalho acadêmico e ao uso pretendido.

## Referências

[1]: https://www.php.net/manual/en/features.commandline.webserver.php "PHP Built-in Web Server"
[2]: https://dev.mysql.com/doc/refman/8.0/en/mysql.html "MySQL Client"
