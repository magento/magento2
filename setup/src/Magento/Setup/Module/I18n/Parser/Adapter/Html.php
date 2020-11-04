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
     * @deprecated Not used anymore because of newly introduced constant
     * @see self::HTML_REGEX_LIST
     */
    const HTML_FILTER = "/i18n:\s?'(?<value>[^'\\\\]*(?:\\\\.[^'\\\\]*)*)'/i";

    private const HTML_REGEX_LIST = [
        // <span><!-- ko i18n: 'Next'--><!-- /ko --></span>
        // <th class="col col-method" data-bind="i18n: 'Select Method'"></th>
        "/i18n:\s?'(?<value>[^'\\\\]*(?:\\\\.[^'\\\\]*)*)'/i",
        // <translate args="'System Messages'"/>
        // <span translate="'Examples'"></span>
        "/translate( args|)=\"'(?<value>[^\"\\\\]*(?:\\\\.[^\"\\\\]*)*)'\"/i"
    ];

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
            }
        }

        foreach (self::HTML_REGEX_LIST as $regex) {
            preg_match_all($regex, $data, $results, PREG_SET_ORDER);

            for ($i = 0, $count = count($results); $i < $count; $i++) {
                if (!empty($results[$i]['value'])) {
                    $this->_addPhrase($results[$i]['value']);
                }
            }
        }
    }
}
