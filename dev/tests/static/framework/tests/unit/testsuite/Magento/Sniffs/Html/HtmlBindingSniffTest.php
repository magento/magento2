<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sniffs\Html;

/**
 * Test the html binding sniff on real files.
 */
class HtmlBindingSniffTest extends AbstractHtmlSniffTest
{
    /**
     * Files to sniff and expected reports.
     *
     * @return array
     */
    public function processDataProvider(): array
    {
        return [
            [
                'test-html-binding.html',
                'test-html-binding-errors.txt'
            ]
        ];
    }
}
