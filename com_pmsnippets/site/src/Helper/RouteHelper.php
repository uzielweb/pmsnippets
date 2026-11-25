<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Multilanguage;

abstract class RouteHelper
{
    public static function getSnippetsRoute(int $catid = 0, string $language = ''): string
    {
        $link = 'index.php?option=com_pmsnippets&view=snippets';

        if ($catid > 0) {
            $link .= '&catid=' . $catid;
        }

        if ($language !== '' && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        return $link;
    }

    public static function getSnippetRoute($id, int $catid = 0, string $language = ''): string
    {
        $link = 'index.php?option=com_pmsnippets&view=snippet&id=' . $id;

        if ($language !== '' && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        return $link;
    }
}
