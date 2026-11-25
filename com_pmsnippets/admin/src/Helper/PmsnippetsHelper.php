<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_pmsnippets
 *
 * @copyright   (C) 2026 Ponto Mega
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace PontoMega\Component\Pmsnippets\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

final class PmsnippetsHelper
{
    public static function getVersionOptions(): array
    {
        return [
            'j6' => Text::_('COM_PMSNIPPETS_VERSION_J6'),
            'j5' => Text::_('COM_PMSNIPPETS_VERSION_J5'),
            'j4' => Text::_('COM_PMSNIPPETS_VERSION_J4'),
            'j3' => Text::_('COM_PMSNIPPETS_VERSION_J3'),
        ];
    }

    public static function getVersionShortLabel(string $code): string
    {
        $map = [
            'j6' => Text::_('COM_PMSNIPPETS_VERSION_SHORT_J6'),
            'j5' => Text::_('COM_PMSNIPPETS_VERSION_SHORT_J5'),
            'j4' => Text::_('COM_PMSNIPPETS_VERSION_SHORT_J4'),
            'j3' => Text::_('COM_PMSNIPPETS_VERSION_SHORT_J3'),
        ];

        return $map[$code] ?? strtoupper($code);
    }

    public static function getCodeLanguageOptions(): array
    {
        return [
            'php'        => Text::_('COM_PMSNIPPETS_LANG_PHP'),
            'javascript' => Text::_('COM_PMSNIPPETS_LANG_JAVASCRIPT'),
            'css'        => Text::_('COM_PMSNIPPETS_LANG_CSS'),
            'xml'        => Text::_('COM_PMSNIPPETS_LANG_XML'),
            'sql'        => Text::_('COM_PMSNIPPETS_LANG_SQL'),
            'htaccess'   => Text::_('COM_PMSNIPPETS_LANG_HTACCESS'),
            'json'       => Text::_('COM_PMSNIPPETS_LANG_JSON'),
            'bash'       => Text::_('COM_PMSNIPPETS_LANG_BASH'),
        ];
    }

    public static function getContextOptions(): array
    {
        return [
            'override'  => Text::_('COM_PMSNIPPETS_CONTEXT_OVERRIDE'),
            'template'  => Text::_('COM_PMSNIPPETS_CONTEXT_TEMPLATE'),
            'module'    => Text::_('COM_PMSNIPPETS_CONTEXT_MODULE'),
            'plugin'    => Text::_('COM_PMSNIPPETS_CONTEXT_PLUGIN'),
            'component' => Text::_('COM_PMSNIPPETS_CONTEXT_COMPONENT'),
            'helper'    => Text::_('COM_PMSNIPPETS_CONTEXT_HELPER'),
            'cli'       => Text::_('COM_PMSNIPPETS_CONTEXT_CLI'),
            'database'  => Text::_('COM_PMSNIPPETS_CONTEXT_DATABASE'),
        ];
    }

    public static function getDifficultyOptions(): array
    {
        return [
            'basic'        => Text::_('COM_PMSNIPPETS_DIFFICULTY_BASIC'),
            'intermediate' => Text::_('COM_PMSNIPPETS_DIFFICULTY_INTERMEDIATE'),
            'advanced'     => Text::_('COM_PMSNIPPETS_DIFFICULTY_ADVANCED'),
        ];
    }

    public static function getFileExtension(string $codeLanguage): string
    {
        $map = [
            'php'        => 'php',
            'javascript' => 'js',
            'css'        => 'css',
            'xml'        => 'xml',
            'sql'        => 'sql',
            'htaccess'   => 'htaccess',
            'json'       => 'json',
            'bash'       => 'sh',
        ];

        return $map[strtolower($codeLanguage)] ?? 'txt';
    }

    /**
     * Normalizes code_blocks JSON/array into a clean indexed array.
     *
     * @param   mixed  $raw  JSON string or array from form subform
     *
     * @return  array<int, array<string, string>>
     */
    public static function parseCodeBlocks($raw): array
    {
        if (\is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw     = \is_array($decoded) ? $decoded : [];
        }

        if (!\is_array($raw)) {
            return [];
        }

        $blocks = [];

        foreach ($raw as $entry) {
            if (!\is_array($entry)) {
                continue;
            }

            $code = (string) ($entry['code'] ?? '');

            if (trim($code) === '') {
                continue;
            }

            $blocks[] = [
                'label'         => trim((string) ($entry['label'] ?? '')),
                'version_tag'   => strtolower(trim((string) ($entry['version_tag'] ?? 'j6'))),
                'code_language' => strtolower(trim((string) ($entry['code_language'] ?? 'php'))),
                'filename_hint' => trim((string) ($entry['filename_hint'] ?? '')),
                'code'          => str_replace(["\r\n", "\r"], "\n", $code),
                'notes'         => trim((string) ($entry['notes'] ?? '')),
            ];
        }

        return $blocks;
    }

    /**
     * Extracts unique Joomla version codes from code_blocks (supports comma-separated tags like "j5,j6").
     *
     * @param   array<int, array<string, string>>  $blocks
     *
     * @return  array<int, string>
     */
    public static function extractVersions(array $blocks): array
    {
        $allowed  = ['j6', 'j5', 'j4', 'j3'];
        $versions = [];

        foreach ($blocks as $block) {
            $tag = (string) ($block['version_tag'] ?? '');
            foreach (preg_split('/[\s,\/]+/', strtolower($tag)) ?: [] as $part) {
                $part = trim($part);
                if (\in_array($part, $allowed, true) && !\in_array($part, $versions, true)) {
                    $versions[] = $part;
                }
            }
        }

        if (empty($versions)) {
            $versions[] = 'j6';
        }

        usort($versions, static fn (string $a, string $b): int => array_search($a, $allowed, true) <=> array_search($b, $allowed, true));

        return $versions;
    }

    /**
     * Builds flat searchable text from all code blocks for MySQL FULLTEXT and Smart Search indexing.
     *
     * @param   array<int, array<string, string>>  $blocks
     */
    public static function buildSearchText(array $blocks): string
    {
        $chunks = [];

        foreach ($blocks as $block) {
            if ($block['label'] !== '') {
                $chunks[] = $block['label'];
            }
            if ($block['filename_hint'] !== '') {
                $chunks[] = $block['filename_hint'];
            }
            if ($block['notes'] !== '') {
                $chunks[] = strip_tags($block['notes']);
            }
            if ($block['code'] !== '') {
                $chunks[] = $block['code'];
            }
        }

        return trim(implode("\n\n", $chunks));
    }
}
