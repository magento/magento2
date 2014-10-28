<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests usage of \Magento\Framework\View\Element\AbstractBlock
 */
namespace Magento\Test\Legacy\Magento\Core\Block;

class AbstractBlockTest extends \PHPUnit_Framework_TestCase
{
    public function testGetChildHtml()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Tests if methods are used with correct count of parameters
             *
             * @param string $file
             */
            function ($file) {
                $result = \Magento\TestFramework\Utility\Classes::getAllMatches(
                    file_get_contents($file),
                    "/(->getChildHtml\([^,()]+, ?[^,()]+,)/i"
                );
                $this->assertEmpty(
                    $result,
                    "3rd parameter is not needed anymore for getChildHtml() in '{$file}': " . print_r($result, true)
                );
                $result = \Magento\TestFramework\Utility\Classes::getAllMatches(
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
            \Magento\TestFramework\Utility\Files::init()->getPhpFiles()
        );
    }
}
