<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;

class Category extends Categories
{
    public function __construct($options = [])
    {
        $options['table']      = '#__pmsnippets_items';
        $options['extension']  = 'com_pmsnippets';
        $options['statefield'] = 'state';

        parent::__construct($options);
    }
}
