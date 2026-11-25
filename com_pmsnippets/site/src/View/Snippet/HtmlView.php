<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Site\View\Snippet;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use PontoMega\Component\Pmsnippets\Site\Helper\RouteHelper;

class HtmlView extends BaseHtmlView
{
    public $item;
    public $state;
    public $params;
    public array $related = [];

    public function display($tpl = null): void
    {
        /** @var \PontoMega\Component\Pmsnippets\Site\Model\SnippetModel $model */
        $model         = $this->getModel();
        $this->item    = $model->getItem();
        $this->state   = $model->getState();
        $this->params  = $this->state->get('params');

        $model->hit((int) $this->item->id);

        if ((int) ComponentHelper::getParams('com_pmsnippets')->get('enable_related', 1) === 1) {
            $this->related = $model->getRelated($this->item, 3);
        }

        $this->prepareDocument();

        parent::display($tpl);
    }

    protected function prepareDocument(): void
    {
        $app = Factory::getApplication();
        $doc = $this->getDocument();
        $wa  = $doc->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_pmsnippets');

        $wa->useStyle('com_pmsnippets.site')
            ->useScript('com_pmsnippets.highlighter')
            ->useScript('com_pmsnippets.app');

        Text::script('COM_PMSNIPPETS_COPIED');
        Text::script('COM_PMSNIPPETS_COPY_CODE');
        Text::script('COM_PMSNIPPETS_COPY_ERROR');
        Text::script('COM_PMSNIPPETS_USEFUL_THANKS');
        Text::script('COM_PMSNIPPETS_USEFUL_ALREADY');

        $this->setDocumentTitle($this->item->title);

        $desc = !empty($this->item->metadesc) ? $this->item->metadesc : $this->item->summary;
        if ($desc !== '') {
            $doc->setDescription($desc);
            $doc->setMetaData('og:description', $desc, 'property');
        }

        $doc->setMetaData('og:title', $this->item->title, 'property');
        $doc->setMetaData('og:type', 'article', 'property');

        if (!empty($this->item->metakey)) {
            $doc->setMetaData('keywords', $this->item->metakey);
        }

        $canonical = Uri::getInstance()->toString(['scheme', 'host', 'port'])
            . Route::_(RouteHelper::getSnippetRoute($this->item->slug, (int) $this->item->catid, (string) $this->item->language));
        $doc->addHeadLink($canonical, 'canonical');

        // Breadcrumb pathway
        $pathway = $app->getPathway();
        $pathway->addItem($this->item->title);

        // Schema.org SoftwareSourceCode JSON-LD
        if ((int) ComponentHelper::getParams('com_pmsnippets')->get('enable_schema_jsonld', 1) === 1) {
            $schema = [
                '@context'            => 'https://schema.org',
                '@type'               => 'SoftwareSourceCode',
                'name'                => $this->item->title,
                'description'         => $desc,
                'programmingLanguage' => strtoupper((string) $this->item->code_language),
                'runtimePlatform'     => 'Joomla! CMS',
                'url'                 => $canonical,
                'datePublished'       => $this->item->created,
                'dateModified'        => $this->item->modified,
                'author'              => [
                    '@type' => 'Organization',
                    'name'  => $app->get('sitename', 'Ponto Mega'),
                    'url'   => Uri::root(),
                ],
            ];
            $doc->addScriptDeclaration(
                json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
                'application/ld+json'
            );
        }
    }
}
