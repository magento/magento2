<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Module\I18n\Parser\Adapter;

use Exception;
use Magento\Email\Model\Template\Filter;

/**
 * Html parser adapter
 */
class Html extends AbstractAdapter
{
    /**
     * Covers
     * <span><!-- ko i18n: 'Next'--><!-- /ko --></span>
     * <th class="col col-method" data-bind="i18n: 'Select Method'"></th>
     * @deprecated Not used anymore because of newly introduced constants
     * @see self::REGEX_I18N_BINDING and self::REGEX_TRANSLATE_TAG_OR_ATTR
     */
    public const HTML_FILTER = "/i18n:\s?'(?<value>[^'\\\\]*(?:\\\\.[^'\\\\]*)*)'/";

    /**
     * Covers
     * <span><!-- ko i18n: 'Next'--><!-- /ko --></span>
     * <th class="col col-method" data-bind="i18n: 'Select Method'"></th>
     */
    public const REGEX_I18N_BINDING = '/i18n:\s?\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/';

    /**
     * Covers
     * <translate args="'System Messages'"/>
     * <span translate="'Examples'"></span>
     */
    public const REGEX_TRANSLATE_TAG_OR_ATTR = '/translate( args|)=\"\'([^\"\\\\]*(?:\\\\.[^\"\\\\]*)*)\'\"/';

    /**
     * @inheritdoc
     */
    protected function _parse()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $data = file_get_contents($this->_file);
        if ($data === false) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new Exception('Failed to load file from disk.');
        }

        $this->extractPhrasesFromTransDirective($data);
        $this->extractPhrases(self::REGEX_I18N_BINDING, $data, 2, 1);
        $this->extractPhrases(self::REGEX_TRANSLATE_TAG_OR_ATTR, $data, 3, 2);
        $this->extractPhrases(Js::REGEX_TRANSLATE_FUNCTION, $data, 3, 2);
    }

    /**
     * Extracts all phrases from trans directives in the given string.
     *
     * @param string $data
     *
     * @return void
     */
    private function extractPhrasesFromTransDirective(string $data): void
    {
        $results = [];
        preg_match_all(Filter::CONSTRUCTION_PATTERN, $data, $results, PREG_SET_ORDER);
        for ($i = 0, $count = count($results); $i < $count; $i++) {
            if ($results[$i][1] === Filter::TRANS_DIRECTIVE_NAME) {
                $directive = [];
                if (preg_match(Filter::TRANS_DIRECTIVE_REGEX, $results[$i][2], $directive) !== 1) {
                    continue;
                }

                $quote = $directive[1];
                $this->_addPhrase($quote . $directive[2] . $quote);
            } elseif (in_array($results[$i][1], ['depend', 'if'], true) && isset($results[$i][3])) {
                // make sure to process trans directives nested inside depend / if directives
                $this->extractPhrasesFromTransDirective($results[$i][3]);
            }
        }
    }

    /**
     * Extracts all phrases with the given regex in the given string.
     *
     * @param string $regex
     * @param string $data
     * @param int $expectedGroupsCount
     * @param int $valueGroupIndex
     */
    protected function extractPhrases(string $regex, string $data, int $expectedGroupsCount, int $valueGroupIndex): void
    {
        preg_match_all($regex, $data, $results, PREG_SET_ORDER);

        for ($i = 0, $count = count($results); $i < $count; $i++) {
            if (count($results[$i]) === $expectedGroupsCount && !empty($results[$i][$valueGroupIndex])) {
                $this->_addPhrase($results[$i][$valueGroupIndex]);
            }
        }
    }
}
