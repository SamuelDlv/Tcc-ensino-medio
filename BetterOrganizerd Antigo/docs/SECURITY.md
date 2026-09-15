# Segurança

## Alterações aplicadas nesta preparação

A credencial do servidor remoto foi removida. A conexão agora utiliza variáveis de ambiente e não imprime detalhes técnicos da falha para o usuário final. O repositório inclui apenas `.env.example`, nunca um `.env` com senha real.

Os formulários de relatório usam consultas preparadas, validam IDs de laboratório e baia, restringem o status aos valores permitidos e escapam mensagens exibidas no HTML. Os caminhos de inclusão foram padronizados para `Conexao` e não dependem de uma diferença de maiúsculas e minúsculas do sistema operacional.

## Riscos que ainda exigem trabalho

O login legado ainda compara a senha diretamente no banco. Isso é inadequado para produção. Migre os registros para hashes e atualize o login para `password_verify()` antes de armazenar usuários reais.

A aplicação ainda precisa de proteção CSRF para operações POST, especialmente cadastro, criação e exclusão de ocorrências. Também é recomendável aplicar `session_regenerate_id(true)` após o login, limitar tentativas de autenticação e configurar cookies de sessão com `HttpOnly`, `Secure` e `SameSite`.

O SQL legado foi mantido compatível com o projeto, mas o script de reconstrução é destrutivo. Faça backup antes de executá-lo em qualquer ambiente que contenha dados.

## Checklist antes da publicação

- Confirme que `.env` não está no índice do Git.
- Pesquise por senhas, tokens, chaves e hosts privados.
- Revogue qualquer credencial que tenha aparecido no MediaFire ou em outro histórico.
- Configure o servidor web para publicar somente `app/`.
- Use HTTPS fora do ambiente local.
- Faça um teste de login com dados fictícios.

## Denúncia de vulnerabilidade

Não publique uma vulnerabilidade com dados sensíveis em uma issue pública. Envie uma descrição mínima ao mantenedor do repositório por um canal privado definido no GitHub.

## Referências

[1]: https://owasp.org/www-project-top-ten/ "OWASP Top 10"
[2]: https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php "PHP MySQLi Prepared Statements"
[3]: https://www.php.net/manual/en/session.security.ini.php "PHP Session Security Settings"
