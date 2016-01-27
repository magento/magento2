<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests usage of \Magento\Framework\View\Element\AbstractBlock
 */
namespace Magento\Test\Legacy\Magento\Core\Block;

use Magento\Framework\App\Utility\Files;

class AbstractBlockTest extends \PHPUnit_Framework_TestCase
{
    public function testGetChildHtml()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Tests if methods are used with correct count of parameters
             *
             * @param string $file
             */
            function ($file) {
                $result = \Magento\Framework\App\Utility\Classes::getAllMatches(
                    file_get_contents($file),
                    "/(->getChildHtml\([^,()]+, ?[^,()]+,)/i"
                );
                $this->assertEmpty(
                    $result,
                    "3rd parameter is not needed anymore for getChildHtml() in '{$file}': " . print_r($result, true)
                );
                $result = \Magento\Framework\App\Utility\Classes::getAllMatches(
                    file_get_contents($file),
                    "/(->getChildChildHtml\([^,()]+, ?[^,()]+, ?[^,()]+,)/i"
                );
                $this->assertEmpty(
                    $result,
                    "4th parameter is not needed anymore for getChildChildHtml() in '{$file}': " . print_r(
                        $result,
                        true
                    )
                );
            },
            Files::init()->getPhpFiles(
                Files::INCLUDE_APP_CODE
                | Files::INCLUDE_PUB_CODE
                | Files::INCLUDE_LIBS
                | Files::INCLUDE_TEMPLATES
                | Files::INCLUDE_TESTS
                | Files::AS_DATA_SET
                | Files::INCLUDE_NON_CLASSES
            )
        );
    }
}
