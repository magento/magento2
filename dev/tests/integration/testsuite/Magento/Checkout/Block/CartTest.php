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
 * Test class for \Magento\Checkout\Block\Cart
 */
namespace Magento\Checkout\Block;

class CartTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMethods()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $child = $layout->createBlock(
            'Magento\Framework\View\Element\Text'
        )->setChild(
            'child1',
            $layout->createBlock('Magento\Framework\View\Element\Text', 'method1')
        )->setChild(
            'child2',
            $layout->createBlock('Magento\Framework\View\Element\Text', 'method2')
        );
        /** @var $block \Magento\Checkout\Block\Cart */
        $block = $layout->createBlock('Magento\Checkout\Block\Cart')->setChild('child', $child);
        $methods = $block->getMethods('child');
        $this->assertEquals(array('method1', 'method2'), $methods);
    }

    public function testGetMethodsEmptyChild()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $childEmpty = $layout->createBlock('Magento\Framework\View\Element\Text');
        /** @var $block \Magento\Checkout\Block\Cart */
        $block = $layout->createBlock('Magento\Checkout\Block\Cart')->setChild('child', $childEmpty);
        $methods = $block->getMethods('child');
        $this->assertEquals(array(), $methods);
    }

    public function testGetMethodsNoChild()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Checkout\Block\Cart */
        $block = $layout->createBlock('Magento\Checkout\Block\Cart');
        $methods = $block->getMethods('child');
        $this->assertEquals(array(), $methods);
    }
}
