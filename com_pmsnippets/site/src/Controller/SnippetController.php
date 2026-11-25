<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

class SnippetController extends BaseController
{
    public function copy(): void
    {
        $app = $this->app;
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);

        if (!Session::checkToken('post') && !Session::checkToken('get')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            $app->close();
        }

        $id = $this->input->getInt('id', 0);

        if ($id <= 0) {
            echo new JsonResponse(null, Text::_('COM_PMSNIPPETS_ERROR_NOT_FOUND'), true);
            $app->close();
        }

        $params = ComponentHelper::getParams('com_pmsnippets');

        if ((int) $params->get('enable_copy_counter', 1) === 1) {
            $db  = Factory::getContainer()->get(DatabaseInterface::class);
            $upd = $db->getQuery(true)
                ->update($db->quoteName('#__pmsnippets_items'))
                ->set($db->quoteName('copies_count') . ' = ' . $db->quoteName('copies_count') . ' + 1')
                ->where($db->quoteName('id') . ' = :id')
                ->where($db->quoteName('state') . ' = 1')
                ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($upd)->execute();

            $sel = $db->getQuery(true)
                ->select($db->quoteName('copies_count'))
                ->from($db->quoteName('#__pmsnippets_items'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $count = (int) $db->setQuery($sel)->loadResult();
        } else {
            $count = 0;
        }

        echo new JsonResponse(['id' => $id, 'copies_count' => $count], Text::_('COM_PMSNIPPETS_COPIED_SUCCESS'));
        $app->close();
    }

    public function useful(): void
    {
        $app = $this->app;
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);

        if (!Session::checkToken('post') && !Session::checkToken('get')) {
            echo new JsonResponse(null, Text::_('JINVALID_TOKEN'), true);
            $app->close();
        }

        $id = $this->input->getInt('id', 0);

        if ($id <= 0) {
            echo new JsonResponse(null, Text::_('COM_PMSNIPPETS_ERROR_NOT_FOUND'), true);
            $app->close();
        }

        $session = $app->getSession();
        $voted   = (array) $session->get('com_pmsnippets.useful_voted', []);
        $db      = Factory::getContainer()->get(DatabaseInterface::class);

        if (!\in_array($id, $voted, true)) {
            $upd = $db->getQuery(true)
                ->update($db->quoteName('#__pmsnippets_items'))
                ->set($db->quoteName('useful_count') . ' = ' . $db->quoteName('useful_count') . ' + 1')
                ->where($db->quoteName('id') . ' = :id')
                ->where($db->quoteName('state') . ' = 1')
                ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($upd)->execute();

            $voted[] = $id;
            $session->set('com_pmsnippets.useful_voted', $voted);
            $alreadyVoted = false;
        } else {
            $alreadyVoted = true;
        }

        $sel = $db->getQuery(true)
            ->select($db->quoteName('useful_count'))
            ->from($db->quoteName('#__pmsnippets_items'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $count = (int) $db->setQuery($sel)->loadResult();

        $msgKey = $alreadyVoted ? 'COM_PMSNIPPETS_USEFUL_ALREADY' : 'COM_PMSNIPPETS_USEFUL_THANKS';
        echo new JsonResponse(['id' => $id, 'useful_count' => $count, 'already' => $alreadyVoted], Text::_($msgKey));
        $app->close();
    }
}
