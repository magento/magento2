<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sniffs\Html;

/**
 * Test the html directive sniff on real files.
 */
class HtmlDirectiveSniffTest extends AbstractHtmlSniffTest
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
                'test-html-directive.html',
                'test-html-directive-errors.txt'
            ],
            [
                'test-html-directive-invalid-json.html',
                'test-html-directive-invalid-json-errors.txt'
            ]
        ];
    }
}
