<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Plugin\Content\Pmsnippets\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use PontoMega\Component\Pmsnippets\Administrator\Helper\PmsnippetsHelper;
use PontoMega\Component\Pmsnippets\Site\Helper\RouteHelper;

final class Pmsnippets extends CMSPlugin implements SubscriberInterface, DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare' => 'onContentPrepare',
        ];
    }

    public function onContentPrepare(ContentPrepareEvent $event): void
    {
        $context = $event->getContext();

        if ($context === 'com_finder.indexer') {
            return;
        }

        $article = $event->getItem();

        if (!isset($article->text) || !str_contains((string) $article->text, '{pmsnippet')) {
            return;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            return;
        }

        $app->getLanguage()->load('com_pmsnippets', JPATH_SITE);
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_pmsnippets');
        $wa->useStyle('com_pmsnippets.site')
            ->useScript('com_pmsnippets.highlighter')
            ->useScript('com_pmsnippets.app');

        Text::script('COM_PMSNIPPETS_COPIED');
        Text::script('COM_PMSNIPPETS_COPY_CODE');

        $article->text = preg_replace_callback(
            '/\{pmsnippet\s+(?:id=(\d+)|alias=["\']?([a-zA-Z0-9_-]+)["\']?)\s*\}/',
            function (array $m): string {
                $id    = !empty($m[1]) ? (int) $m[1] : 0;
                $alias = !empty($m[2]) ? trim($m[2]) : '';

                return $this->renderSnippetEmbed($id, $alias);
            },
            (string) $article->text
        );
    }

    private function renderSnippetEmbed(int $id, string $alias): string
    {
        $db     = $this->getDatabase();
        $app    = $this->getApplication();
        $user   = $app->getIdentity();
        $groups = $user ? $user->getAuthorisedViewLevels() : [1];

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__pmsnippets_items'))
            ->where($db->quoteName('state') . ' = 1')
            ->whereIn($db->quoteName('access'), $groups);

        if ($id > 0) {
            $query->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
        } elseif ($alias !== '') {
            $langTag = $app->getLanguage()->getTag();
            $query->where($db->quoteName('alias') . ' = :alias')
                ->whereIn($db->quoteName('language'), [$langTag, '*'], ParameterType::STRING)
                ->bind(':alias', $alias, ParameterType::STRING);
        } else {
            return '';
        }

        $item = $db->setQuery($query, 0, 1)->loadObject();

        if (!$item) {
            return '';
        }

        $blocks = PmsnippetsHelper::parseCodeBlocks($item->code_blocks ?? '');

        if (empty($blocks)) {
            return '';
        }

        $copyUrl   = htmlspecialchars(Route::_('index.php?option=com_pmsnippets&task=snippet.copy', false), ENT_QUOTES, 'UTF-8');
        $token     = htmlspecialchars(Session::getFormToken(), ENT_QUOTES, 'UTF-8');
        $detailUrl = htmlspecialchars(Route::_(RouteHelper::getSnippetRoute($item->id . ':' . $item->alias, (int) $item->catid, (string) $item->language)), ENT_QUOTES, 'UTF-8');
        $titleEsc  = htmlspecialchars((string) $item->title, ENT_QUOTES, 'UTF-8');

        $tabsHtml   = '';
        $panelsHtml = '';

        foreach ($blocks as $idx => $block) {
            $activeClass = $idx === 0 ? ' is-active' : '';
            $selected    = $idx === 0 ? 'true' : 'false';
            $tabLabel    = htmlspecialchars($block['label'] ?: strtoupper($block['version_tag']), ENT_QUOTES, 'UTF-8');
            $langEsc     = htmlspecialchars($block['code_language'] ?: $item->code_language, ENT_QUOTES, 'UTF-8');
            $codeEsc     = htmlspecialchars($block['code'], ENT_QUOTES, 'UTF-8');

            $tabsHtml .= '<button type="button" class="pms-workbench__tab' . $activeClass . '" role="tab" aria-selected="' . $selected . '" data-pms-tab="' . $idx . '">' . $tabLabel . '</button>';
            $panelsHtml .= '<div class="pms-workbench__panel' . $activeClass . '" role="tabpanel" data-pms-panel="' . $idx . '">'
                . '<pre class="pms-code-pre pms-code-pre--full"><code class="pms-code" data-pms-lang="' . $langEsc . '">' . $codeEsc . '</code></pre>'
                . '<textarea class="visually-hidden" aria-hidden="true" data-pms-raw-code>' . $codeEsc . '</textarea>'
                . '</div>';
        }

        $copyLabel = htmlspecialchars(Text::_('COM_PMSNIPPETS_COPY_CODE'), ENT_QUOTES, 'UTF-8');
        $openLabel = htmlspecialchars(Text::_('COM_PMSNIPPETS_OPEN_SNIPPET'), ENT_QUOTES, 'UTF-8');

        return '<div class="pms-embed-card" data-copy-url="' . $copyUrl . '" data-csrf-token="' . $token . '">'
            . '<div class="pms-embed-card__head">'
            . '<h4 class="pms-embed-card__title"><a href="' . $detailUrl . '">' . $titleEsc . '</a></h4>'
            . '<a href="' . $detailUrl . '" class="pms-wb-btn">' . $openLabel . ' &rarr;</a>'
            . '</div>'
            . '<div class="pms-code-workbench m-0 border-0" data-pms-workbench>'
            . '<div class="pms-workbench__bar">'
            . '<div class="pms-workbench__tabs" role="tablist">' . $tabsHtml . '</div>'
            . '<div class="pms-workbench__actions">'
            . '<button type="button" class="pms-wb-btn pms-wb-btn--primary" data-pms-copy-active data-snippet-id="' . (int) $item->id . '">'
            . '<i class="fa-regular fa-copy" aria-hidden="true"></i> <span data-pms-copy-label>' . $copyLabel . '</span>'
            . '</button>'
            . '</div>'
            . '</div>'
            . $panelsHtml
            . '</div>'
            . '</div>';
    }
}
