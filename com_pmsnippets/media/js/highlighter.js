/**
 * Ponto Mega Snippets - Lightweight Matte Syntax Highlighter
 * Zero external CDN dependencies, vanilla JS, PUA-encoded token stashing
 * Supports: php, javascript, css, xml, sql, htaccess, json, bash
 */
(function () {
  const escapeHtml = (str) =>
    str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');

  const toPua = (n) =>
    String(n)
      .split('')
      .map((d) => String.fromCharCode(0xe100 + Number(d)))
      .join('');

  const fromPua = (s) =>
    Number(
      Array.from(s)
        .map((c) => c.charCodeAt(0) - 0xe100)
        .join('')
    );

  const KEYWORDS = {
    php: /\b(use|namespace|class|final|abstract|interface|trait|extends|implements|public|protected|private|static|function|return|if|elseif|else|foreach|for|while|switch|case|default|break|continue|new|echo|print|try|catch|finally|throw|true|false|null|array|match|fn|readonly|const|define|defined|require|require_once|include|include_once|or|and|as|instanceof|isset|empty|unset|declare|strict_types|endif|endforeach|endfor|endwhile|endswitch|void|int|string|bool|float|mixed|self|parent)\b/g,
    javascript: /\b(const|let|var|function|return|if|else|for|while|switch|case|default|break|continue|new|try|catch|finally|throw|true|false|null|undefined|async|await|import|export|from|class|extends|this|typeof|instanceof|of|in)\b/g,
    sql: /\b(SELECT|FROM|WHERE|JOIN|LEFT|INNER|RIGHT|ON|AND|OR|ORDER|BY|GROUP|HAVING|LIMIT|INSERT|INTO|VALUES|UPDATE|SET|DELETE|CREATE|ALTER|TABLE|IF|NOT|EXISTS|PRIMARY|KEY|INDEX|FULLTEXT|AS|DESC|ASC|DISTINCT|IN|IS|NULL|CONVERT|TO|CHARACTER|COLLATE|ROUND|SUM|COUNT)\b/gi,
    css: /\b(display|position|color|background|background-color|border|border-color|padding|margin|font-family|font-size|font-weight|grid|flex|width|height|max-width|min-height|important|root|var)\b/g,
    json: /\b(true|false|null)\b/g,
    bash: /\b(php|curl|mysql|mysqldump|chmod|chown|sudo|cd|ls|grep|awk|sed|export|if|then|fi|for|do|done|echo)\b/g,
    htaccess: /\b(RewriteEngine|RewriteCond|RewriteRule|Header|Set|Always|IfModule|ExpiresActive|ExpiresByType|AddOutputFilterByType|Require|FilesMatch|Options|Order|Deny|Allow|RedirectMatch)\b/g,
    xml: null,
  };

  function highlightText(raw, lang) {
    const tokens = [];
    const stash = (html) => {
      const idx = tokens.length;
      tokens.push(html);
      return `\uE000${toPua(idx)}\uE001`;
    };

    let text = escapeHtml(raw);
    const l = (lang || 'php').toLowerCase();

    // 1. Comments
    if (l === 'xml') {
      text = text.replace(/(&lt;!--[\s\S]*?--&gt;)/g, (m) => stash(`<span class="pms-tok-cm">${m}</span>`));
    } else if (l === 'bash' || l === 'htaccess') {
      text = text.replace(/(^|\n)(\s*#[^\n]*)/g, (_, br, c) => br + stash(`<span class="pms-tok-cm">${c}</span>`));
    } else if (l === 'sql') {
      text = text.replace(/(\/\*[\s\S]*?\*\/)/g, (m) => stash(`<span class="pms-tok-cm">${m}</span>`));
      text = text.replace(/(--[^\n]*)/g, (m) => stash(`<span class="pms-tok-cm">${m}</span>`));
    } else if (l !== 'json') {
      text = text.replace(/(\/\*[\s\S]*?\*\/)/g, (m) => stash(`<span class="pms-tok-cm">${m}</span>`));
      text = text.replace(/(^|[^:\\])(\/\/[^\n]*)/g, (_, p, c) => p + stash(`<span class="pms-tok-cm">${c}</span>`));
    }

    // 2. Strings
    text = text.replace(/('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, (m) => stash(`<span class="pms-tok-str">${m}</span>`));

    // 3. Tags (PHP / XML / HTML)
    if (l === 'php' || l === 'xml') {
      text = text.replace(/(&lt;\?php|&lt;\?xml|\?&gt;|&lt;\/?[a-zA-Z0-9:_-]+(?:\s[^&]*?)?&gt;)/g, (m) => stash(`<span class="pms-tok-tag">${m}</span>`));
    }

    // 4. Variables (PHP / Bash / CSS custom properties)
    if (l === 'php') {
      text = text.replace(/(\$[a-zA-Z_][a-zA-Z0-9_]*)/g, (m) => stash(`<span class="pms-tok-var">${m}</span>`));
    } else if (l === 'bash') {
      text = text.replace(/(\$[a-zA-Z_][a-zA-Z0-9_]*|\$\{[^}]+\})/g, (m) => stash(`<span class="pms-tok-var">${m}</span>`));
    } else if (l === 'css') {
      text = text.replace(/(--[a-zA-Z0-9_-]+)/g, (m) => stash(`<span class="pms-tok-var">${m}</span>`));
    }

    // 5. Keywords
    const kwRegex = Object.prototype.hasOwnProperty.call(KEYWORDS, l) ? KEYWORDS[l] : KEYWORDS.php;
    if (kwRegex) {
      text = text.replace(kwRegex, (m) => stash(`<span class="pms-tok-kw">${m}</span>`));
    }

    // 6. Method and function calls (skip XML/JSON/htaccess/bash)
    if (l === 'php' || l === 'javascript' || l === 'sql' || l === 'css') {
      text = text.replace(/\b([a-zA-Z_][a-zA-Z0-9_]*)(?=\s*\()/g, (m) => stash(`<span class="pms-tok-fn">${m}</span>`));
    }

    // 7. Numbers (skip htaccess flags/IPs/paths unless standalone)
    if (l !== 'xml') {
      text = text.replace(/\b(\d+(?:\.\d+)?)\b/g, (m) => stash(`<span class="pms-tok-num">${m}</span>`));
    }

    // 8. Restore all stashed tokens
    return text.replace(/\uE000([\uE100-\uE109]+)\uE001/g, (_, pua) => tokens[fromPua(pua)] || '');
  }

  function highlightContainer(root) {
    const scope = root || document;
    scope.querySelectorAll('code.pms-code[data-pms-lang]').forEach((el) => {
      if (el.dataset.pmsHighlighted === '1') {
        return;
      }
      const raw = el.textContent || '';
      el.innerHTML = highlightText(raw, el.getAttribute('data-pms-lang') || 'php');
      el.dataset.pmsHighlighted = '1';
    });
  }

  window.PMSnippetsHighlighter = { highlightContainer };
  document.addEventListener('DOMContentLoaded', () => highlightContainer(document));
})();
