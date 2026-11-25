# Ponto Mega Snippets (`pkg_pmsnippets`)

Native technical code snippet library suite for **Joomla! 5 and Joomla! 6**, developed by [Uziel Almeida Oliveira (Ponto Mega)](https://pontomega.com.br).

- **Live Demo (English):** https://pontomega.com.br/en/joomla-snippets
- **Live Demo (Portuguese):** https://pontomega.com.br/pt/joomla-snippets
- **Official Download Page:** https://pontomega.com.br/en/downloads/ponto-mega-snippets-joomla-6-en
- **Joomla Update Server XML:** https://raw.githubusercontent.com/uzielweb/pmsnippets/main/updates.xml
- **Changelog XML:** https://raw.githubusercontent.com/uzielweb/pmsnippets/main/changelog.xml

## Included Extensions

| Extension | Type | Purpose |
|---|---|---|
| `com_pmsnippets` | Component | Native Joomla! 6 DI/MVC catalog with `FULLTEXT` and raw code AJAX search, faceted filters, versioned code tabs (e.g. Joomla! 5/6 and Joomla! 3), zero-CDN local syntax highlighter (8 languages), one-click copy, direct file download, useful voting, multilingual associations (`en-GB` / `pt-BR`), and Schema.org `SoftwareSourceCode` JSON-LD. |
| `mod_pmsnippets` | Site Module | Interactive showcase module for home pages (instant search, live metrics, category pills, and tabbed code console) plus `grid` and `compact` layouts. |
| `plg_finder_pmsnippets` | Smart Search Plugin | Indexes title, summary, description, searchable code, and 6 taxonomies (`Type`, `Category`, `Joomla Version`, `Code Language`, `Snippet Context`, `Language`). |
| `plg_content_pmsnippets` | Content Plugin | Embeds snippets directly inside Joomla! articles using `{pmsnippet alias="..."}` or `{pmsnippet id=1}`. |

## Requirements

- Joomla! 5.x or Joomla! 6.x
- PHP 8.1+ (tested up to PHP 8.5)
- MySQL 8.0+ / 9.x or MariaDB 10.6+ (`InnoDB`, `utf8mb4`)

## Installation

1. Download `pkg_pmsnippets_v1.0.0.zip` from [GitHub Releases](https://github.com/uzielweb/pmsnippets/releases).
2. In your Joomla! Administrator panel, go to **System > Install > Extensions** and upload the `.zip` package.
3. Enable **Smart Search - Ponto Mega Snippets** and **Content - Ponto Mega Snippets**, then run the Smart Search indexer (`php cli/joomla.php finder:index purge`).

## Automatic Updates

This package ships with native Joomla! Updater integration hosted directly on GitHub:

- **Update Server:** `https://raw.githubusercontent.com/uzielweb/pmsnippets/main/updates.xml`
- **Changelog:** `https://raw.githubusercontent.com/uzielweb/pmsnippets/main/changelog.xml`

## Building Locally from Source

```bash
python build.py
```

The installable package is generated at `dist/pkg_pmsnippets_v1.0.0.zip`.

## License

Distributed under the **GPL-2.0-or-later** license. See [LICENSE](LICENSE) for details.
