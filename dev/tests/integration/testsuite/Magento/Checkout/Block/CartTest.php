<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            \Magento\Framework\View\LayoutInterface::class
        );
        $child = $layout->createBlock(
            \Magento\Framework\View\Element\Text::class
        )->setChild(
            'child1',
            $layout->createBlock(
                \Magento\Framework\View\Element\Text::class,
                'method1'
            )
        )->setChild(
            'child2',
            $layout->createBlock(
                \Magento\Framework\View\Element\Text::class,
                'method2'
            )
        );
        /** @var $block \Magento\Checkout\Block\Cart */
        $block = $layout->createBlock(\Magento\Checkout\Block\Cart::class)->setChild('child', $child);
        $methods = $block->getMethods('child');
        $this->assertEquals(['method1', 'method2'], $methods);
    }

    public function testGetMethodsEmptyChild()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        $childEmpty = $layout->createBlock(\Magento\Framework\View\Element\Text::class);
        /** @var $block \Magento\Checkout\Block\Cart */
        $block = $layout->createBlock(\Magento\Checkout\Block\Cart::class)->setChild('child', $childEmpty);
        $methods = $block->getMethods('child');
        $this->assertEquals([], $methods);
    }

    public function testGetMethodsNoChild()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\Checkout\Block\Cart */
        $block = $layout->createBlock(\Magento\Checkout\Block\Cart::class);
        $methods = $block->getMethods('child');
        $this->assertEquals([], $methods);
    }

    public function testGetPagerHtml()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var $block \Magento\Checkout\Block\Cart */
        $block = $layout->createBlock(\Magento\Checkout\Block\Cart::class);
        $pager = $block->getPagerHtml();
        $this->assertEquals('', $pager);
    }
}
