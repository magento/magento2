<?php
/**
 * Test block names exists
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\Layout;

class BlockNamesTest extends \PHPUnit\Framework\TestCase
{
    public function testBlocksHasName()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Test validate that blocks without name doesn't exist in layout file
             *
             * @param string $layoutFile
             */
            function ($layoutFile) {
                $dom = new \DOMDocument();
                $dom->load($layoutFile);
                $xpath = new \DOMXpath($dom);
                $count = $xpath->query('//block[not(@name)]')->length;

                if ($count) {
                    $this->fail('Following file contains ' . $count . ' blocks without name. ' .
                        'File Path:' . "\n" . $layoutFile);
                }
            },
            \Magento\Framework\App\Utility\Files::init()->getLayoutFiles()
        );
    }
}
