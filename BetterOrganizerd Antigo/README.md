# Better Organized

Sistema web acadêmico desenvolvido como Trabalho de Conclusão de Curso do ensino médio em 2023. O projeto organiza ocorrências de equipamentos em laboratórios, permitindo registrar problemas, identificar a baia afetada e indicar se o equipamento está com defeito ou em manutenção.

## Estado do projeto

Esta versão foi preparada para publicação no GitHub. A aplicação continua sendo PHP procedural com MySQL/MariaDB e não exige framework ou gerenciador de dependências. A organização foi separada em `app/`, `database/` e `docs/` para facilitar manutenção e instalação.

## Requisitos

- PHP 7.4 ou superior, com as extensões `mysqli` e `session` habilitadas;
- MySQL 8 ou MariaDB 10.4 ou superior;
- Um servidor web local, como Apache, Nginx ou o servidor embutido do PHP;
- Git, caso o projeto seja clonado do GitHub.

## Instalação rápida

Clone o repositório e entre nele:

```bash
git clone https://github.com/SEU-USUARIO/better-organized.git
cd better-organized
```

Copie o modelo de configuração e edite a senha do seu MySQL:

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

## Criar o banco de dados

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

## Executar localmente

A partir da raiz do repositório, inicie o servidor embutido do PHP apontando para `app/`:

```bash
php -S 127.0.0.1:8000 -t app
```

Depois acesse `http://127.0.0.1:8000` no navegador.

Também é possível configurar o Apache para usar `app/` como DocumentRoot. Não publique a raiz do repositório como DocumentRoot, porque ela contém documentação e arquivos de configuração destinados ao desenvolvimento.

## Primeiro acesso

O cadastro está disponível na tela inicial. O ID do usuário é gerado automaticamente pelo banco e não precisa ser informado. Crie um usuário com função `1` para professor ou `0` para suporte. Em um ambiente real, as senhas devem ser armazenadas com `password_hash()` e verificadas com `password_verify()`; o código legado ainda usa comparação direta e essa migração deve ser feita antes de qualquer uso com dados reais.

## Estrutura

```text
.
├── app/
│   ├── config/              # Conexão segura e centralizada
│   ├── LoginCadastro/       # Estilos e scripts da autenticação
│   ├── RotaProfessor/       # Fluxo do professor
│   ├── RotaSuporte/         # Fluxo do suporte
│   └── index.php            # Login e cadastro
├── database/
│   └── schema.sql           # Estrutura destrutiva do banco
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

## Publicar no GitHub

Antes do primeiro commit, confirme que não há senhas ou dumps locais:

```bash
git init
git add .
git diff --cached --check
git grep -n -I -i -E 'password|senha|api[_-]?key|token|mysqli_connect|45\.152\.44\.154|Grupo04' || true
git commit -m "Prepara projeto para publicação"
git branch -M main
git remote add origin https://github.com/SEU-USUARIO/better-organized.git
git push -u origin main
```

Substitua `SEU-USUARIO` pelo seu usuário real. Se alguma credencial antiga já tiver sido publicada em outro repositório, troque-a no provedor correspondente; remover o texto de um novo commit não invalida um segredo já exposto no histórico.

## Limitações conhecidas

O sistema original foi criado como projeto acadêmico e mantém uma arquitetura procedural. A documentação registra o comportamento atual sem prometer recursos que não existem. Recomenda-se fazer a migração de senhas, adicionar proteção CSRF e separar autenticação, regras de negócio e apresentação antes de usar a aplicação fora de um ambiente de estudo.

## Licença

Defina uma licença antes de publicar o repositório. Para um TCC, MIT costuma ser uma opção simples, mas a decisão deve refletir a intenção dos autores.

## Referências

[1]: https://www.php.net/manual/en/features.commandline.webserver.php "PHP Built-in Web Server"
[2]: https://dev.mysql.com/doc/refman/8.0/en/mysql.html "MySQL Client"
[3]: https://docs.github.com/en/repositories/working-with-files/managing-files/adding-a-file-to-a-repository "GitHub Documentation: Adding a File to a Repository"
