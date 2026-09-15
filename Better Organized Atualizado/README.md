# BetterOrganized — Versão Atualizada

## Sistema de gestão de computadores em laboratórios

O **BetterOrganized** é a versão atualizada de um sistema de gestão para laboratórios de informática. A aplicação permite acompanhar computadores, baias, componentes de hardware, ocorrências técnicas e histórico de atendimento em uma interface web com perfis de **Professor** e **Suporte**.

> **Sobre este diretório:** esta versão faz parte do repositório [`Tcc-ensino-medio`](https://github.com/SamuelDlv/Tcc-ensino-medio). Ela não é um repositório Git independente. Para obter o projeto, clone o repositório principal e entre nesta pasta.

```bash
git clone https://github.com/SamuelDlv/Tcc-ensino-medio.git
cd "Tcc-ensino-medio/Better Organized Atualizado"
```

O projeto foi organizado para ser executado em PHP com MySQL ou MariaDB, sem dependências externas obrigatórias e sem frameworks de aplicação. O PDF dos relatórios é gerado internamente pela classe `SimplePdf`.

> Esta é uma versão acadêmica e de protótipo funcional. Antes de colocá-la em produção, altere as credenciais de demonstração, implemente autenticação com hash de senha e configure HTTPS.

## Funcionalidades

O perfil de Professor pode consultar os laboratórios, visualizar a disposição das máquinas e baias, abrir ocorrências, consultar relatórios e baixar relatórios em PDF. O perfil de Suporte possui as mesmas consultas e também pode cadastrar, editar e excluir máquinas, além de acompanhar e concluir ocorrências.

O cadastro de cada máquina possui identificação automática por ID, laboratório, status e componentes de hardware. A baia é atribuída automaticamente à primeira posição livre dentro do laboratório. O número da baia pode se repetir em laboratórios diferentes, pois a posição é relativa a cada sala.

O laboratório é apresentado com uma visualização responsiva. Os computadores são organizados em fileiras laterais com um corredor central. Os status são apresentados como **Funcionando**, **Com defeito** e **Manutenção**.

## Requisitos

- PHP 8.1 ou superior;
- extensão PHP `mysqli`;
- MySQL 8 ou MariaDB 10.4 ou superior;
- servidor web apontando para a pasta `public/` como document root;
- navegador moderno com suporte a CSS Grid e Flexbox.

## Instalação rápida

### 1. Obter o projeto

Se você ainda não clonou o repositório principal:

```bash
git clone https://github.com/SamuelDlv/Tcc-ensino-medio.git
cd "Tcc-ensino-medio/Better Organized Atualizado"
```

Se você já estiver dentro da pasta `Better Organized Atualizado`, continue a partir da próxima etapa.

### 2. Criar o banco

O projeto possui um único instalador SQL consolidado. Ele cria o banco `betterorganized`, as tabelas, quatro laboratórios, usuários de teste e vinte máquinas de demonstração no Laboratório 1.

```bash
mysql -u root -p < database/schema.sql
```

O arquivo pode ser aberto e executado pelo MySQL Workbench, DBeaver ou outro cliente compatível.

### 3. Configurar as credenciais

As credenciais não ficam no código versionado. Copie o modelo de ambiente e configure os valores no seu sistema:

```bash
cp .env.example .env
```

O arquivo `.env` é apenas uma referência local e está protegido pelo `.gitignore`. A aplicação lê as seguintes variáveis:

```text
BETTERORGANIZED_DB_HOST
BETTERORGANIZED_DB_USER
BETTERORGANIZED_DB_PASS
BETTERORGANIZED_DB_NAME
```

Em um terminal Linux ou macOS, elas podem ser exportadas assim:

```bash
export BETTERORGANIZED_DB_HOST=127.0.0.1
export BETTERORGANIZED_DB_USER=betterorganized_user
export BETTERORGANIZED_DB_PASS='sua-senha-local'
export BETTERORGANIZED_DB_NAME=betterorganized
```

O PHP utilizado no desenvolvimento local aceita os valores padrão definidos em `config/database.php`, mas o uso recomendado para qualquer ambiente compartilhado é configurar um usuário de banco dedicado e uma senha fora do repositório.

### 4. Iniciar o servidor de desenvolvimento

A partir desta pasta:

```bash
php -S 127.0.0.1:8000 -t public
```

Acesse `http://127.0.0.1:8000` no navegador.

Em Apache ou Nginx, configure `public/` como a raiz pública. A pasta `config/` deve permanecer fora da raiz pública sempre que possível.

## Usuários de demonstração

O SQL consolidado cria contas apenas para demonstração local:

| Perfil | E-mail | Senha inicial |
|---|---|---|
| Professor | `professor@teste.com` | `123456` |
| Suporte | `suporte@teste.com` | `123456` |

Essas credenciais são previsíveis e **não devem ser usadas em produção**. Altere ou remova as contas antes de publicar um ambiente acessível pela internet.

## Estrutura do projeto

```text
Better Organized Atualizado/
├── config/
│   ├── database.php       # conexão por variáveis de ambiente
│   ├── helpers.php        # regras compartilhadas do sistema
│   └── SimplePdf.php      # gerador interno de PDF
├── database/
│   └── schema.sql         # instalador SQL único e completo
├── public/
│   ├── index.php          # login e cadastro
│   ├── logout.php         # encerramento da sessão
│   ├── assets/            # CSS e JavaScript do navegador
│   ├── professor/         # telas e ações do perfil Professor
│   └── suporte/           # telas e ações do perfil Suporte
├── .env.example           # modelo sem segredos
├── .gitignore             # proteção contra arquivos locais
└── README.md              # esta documentação
```

## Modelo de dados

A tabela `Lab` representa os laboratórios. A tabela `Maquina` representa os computadores e referencia o laboratório e o conjunto de peças. A tabela `Baia` representa a posição física de uma máquina dentro de um laboratório.

A combinação `ID_LAB + numero` identifica uma posição de baia. Por isso, a baia 1 pode existir no Laboratório 1 e no Laboratório 2 sem conflito. O `ID_MAQ` e o `ID_BAIA` são identificadores internos únicos e não substituem o número físico da baia.

A tabela `Ocorrencias` registra problemas e manutenções. A tabela `Historico` preserva os eventos importantes mesmo quando uma ocorrência é concluída ou removida da lista ativa.

## Fluxo de uso

O usuário começa na tela de login. Após a autenticação, escolhe um laboratório e visualiza os computadores e suas baias. O Professor pode abrir uma ocorrência vinculada a uma máquina. O Suporte pode cadastrar componentes, editar máquinas, acompanhar ocorrências e registrar a conclusão do atendimento.

O relatório geral agrupa as ocorrências do laboratório selecionado. O botão de PDF usa a classe interna `SimplePdf`, sem Composer ou biblioteca externa. O arquivo é gerado com título, data, tabela, status colorido, quebra de texto e paginação automática.

## Verificações antes de publicar

Execute o lint do PHP em todas as páginas:

```bash
find . -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```

Confirme que nenhum segredo está versionado:

```bash
git grep -n -i -E 'senha real|password|secret|api[_-]?key|token|BEGIN PRIVATE KEY'
```

Confira o estado do repositório antes do primeiro push:

```bash
git status
git diff --check
```

Nunca publique um arquivo `.env`, dumps do banco, logs, backups ou credenciais reais. Se uma senha já tiver sido enviada para um repositório remoto, altere-a imediatamente; apagar o arquivo em um commit posterior não remove o segredo do histórico.

## Segurança e próximos passos

A versão atual foi preparada para não conter a senha do banco no código. Entretanto, a autenticação de demonstração ainda compara a senha diretamente com o valor armazenado no banco. Para produção, substitua esse fluxo por `password_hash()` e `password_verify()`, implemente proteção contra tentativas repetidas, use HTTPS e crie permissões mais granulares.

Também é recomendável configurar backups do banco, registrar erros em um destino fora da pasta pública, validar o tamanho dos uploads caso novos anexos sejam adicionados e revisar as políticas de sessão do servidor.

## Licença

Este repositório não define uma licença por padrão. Antes de permitir reutilização pública, escolha uma licença adequada ao trabalho acadêmico e ao uso pretendido.

## Referências

[1]: https://www.php.net/manual/en/mysqli.quickstart.connections.php "PHP Manual — MySQLi connections"

[2]: https://www.php.net/manual/en/function.password-hash.php "PHP Manual — password_hash"

[3]: https://dev.mysql.com/doc/refman/8.0/en/ "MySQL 8.0 Reference Manual"
