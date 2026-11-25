# Ponto Mega Snippets (`pkg_pmsnippets`)

Biblioteca técnica de snippets de código nativa para **Joomla! 5 e Joomla! 6**, desenvolvida por [Uziel Almeida Oliveira (Ponto Mega)](https://pontomega.com.br).

- **Demonstração ao vivo:** https://pontomega.com.br/pt/joomla-snippets
- **Versão em Inglês:** https://pontomega.com.br/en/joomla-snippets
- **Download oficial:** https://pontomega.com.br/pt/downloads/ponto-mega-snippets-joomla-6

## Extensões incluídas no pacote

| Extensão | Tipo | Função |
|---|---|---|
| `com_pmsnippets` | Componente | Catálogo MVC nativo Joomla! 6 com busca `FULLTEXT` + busca literal em código via AJAX, filtros facetados, abas de código por versão (ex: Joomla! 5/6 e Joomla! 3), syntax highlighter local sem CDN (8 linguagens), cópia em 1 clique, download do arquivo, voto de utilidade e Schema.org `SoftwareSourceCode` JSON-LD. |
| `mod_pmsnippets` | Módulo Site | Vitrine interativa para Home (busca direta, métricas, categorias e console de código com abas) além de layouts `grid` e `compact`. |
| `plg_finder_pmsnippets` | Plugin Smart Search | Indexa título, resumo, descrição, código pesquisável e 6 taxonomias (`Type`, `Category`, `Joomla Version`, `Code Language`, `Snippet Context`, `Language`). |
| `plg_content_pmsnippets` | Plugin Content | Incorpora snippets diretamente dentro de artigos com `{pmsnippet alias="..."}` ou `{pmsnippet id=1}`. |

## Requisitos

- Joomla! 5.x ou Joomla! 6.x
- PHP 8.1+ (testado até PHP 8.5)
- MySQL 8.0+ / 9.x ou MariaDB 10.6+ (`InnoDB`, `utf8mb4`)

## Instalação

1. Baixe `pkg_pmsnippets_v1.0.0.zip` na aba [Releases](https://github.com/uzielweb/pmsnippets/releases).
2. No administrador do Joomla!, acesse **Sistema > Instalar > Extensões** e envie o pacote `.zip`.
3. Ative os plugins **Smart Search - Ponto Mega Snippets** e **Conteúdo - Ponto Mega Snippets** e execute a indexação da Busca Inteligente (`php cli/joomla.php finder:index purge`).

## Empacotamento local a partir do código-fonte

```bash
python build.py
```

O pacote instalável é gerado em `dist/pkg_pmsnippets_v1.0.0.zip`.

## Licença

Distribuído sob licença **GPL-2.0-or-later**.
