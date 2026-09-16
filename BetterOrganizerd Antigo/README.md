# Better Organized — Versão Original

Esta é a versão original do **Better Organized**, projeto que desenvolvi em 2023 como Trabalho de Conclusão de Curso durante o ensino médio.

A proposta era ajudar a escola a acompanhar os computadores dos laboratórios, registrando ocorrências, localização das máquinas e situações como defeito ou manutenção.

> Esta pasta faz parte do repositório [`Tcc-ensino-medio`](https://github.com/SamuelDlv/Tcc-ensino-medio) e não é um repositório Git separado.

## Estado do projeto

Esta versão foi preservada como registro do TCC de 2023. A aplicação utiliza PHP procedural com MySQL/MariaDB e não depende de frameworks.

## Requisitos

- PHP 7.4+ com `mysqli` e `session`
- MySQL 8 ou MariaDB 10.4+
- Servidor web local ou servidor embutido do PHP

## Execução

A partir desta pasta:

```bash
cp .env.example .env
php -S 127.0.0.1:8000 -t app
```

Configure as variáveis do banco no ambiente local antes de iniciar a aplicação.

## Estrutura

```text
BetterOrganizerd Antigo/
├── app/
├── database/
├── docs/
├── .env.example
├── .gitignore
└── README.md
```

A documentação de arquitetura e segurança fica em `docs/`.

## Relação com a versão atualizada

A versão revisada está em `../Better Organized Atualizado/`. Mantive as duas versões no mesmo repositório para preservar a evolução do projeto.

## Autor

Projeto desenvolvido por **SamuelDlv** em 2023.

GitHub: https://github.com/SamuelDlv
