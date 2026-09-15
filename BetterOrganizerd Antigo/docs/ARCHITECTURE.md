# Arquitetura do Better Organized

## Objetivo

A organização separa o código executável, o banco e a documentação. Essa separação reduz o risco de publicar arquivos operacionais no DocumentRoot e facilita a evolução do projeto sem misturar scripts PHP com artefatos de instalação.

## Diretórios

`app/` é a raiz pública da aplicação. O servidor web deve apontar para esse diretório. `app/config/` concentra a conexão com o banco. As rotas de professor e suporte permanecem separadas porque representam fluxos distintos do sistema original.

`database/` contém scripts de criação do banco. O schema é destrutivo por decisão explícita: ele serve para reconstruir um ambiente perdido e não para migrações incrementais.

`docs/` contém documentação de manutenção, execução e segurança. Arquivos `.env` ficam fora do Git por meio do `.gitignore`; apenas `.env.example` é versionado.

## Fluxo de dados

O usuário autentica-se em `app/index.php`. O ID da sessão identifica o usuário nas rotas. Os relatórios são inseridos em `Ocorrencias` com o laboratório, a baia, a data, a descrição e o status. O mapa consulta esse status e aplica a cor correspondente.

## Próximos passos recomendados

A próxima evolução arquitetural deve introduzir uma camada de autenticação com `password_hash()` e `password_verify()`, uma camada de acesso a dados e validação centralizada. A aplicação também deve adotar tokens CSRF e uma política de expiração de sessão.

## Referências

[1]: https://www.php.net/manual/en/function.password-hash.php "PHP password_hash"
[2]: https://www.php.net/manual/en/function.password-verify.php "PHP password_verify"
