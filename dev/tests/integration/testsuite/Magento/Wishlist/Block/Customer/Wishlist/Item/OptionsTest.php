<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Wishlist\Block\Customer\Wishlist\Item\Options.
 */
namespace Magento\Wishlist\Block\Customer\Wishlist\Item;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTemplate()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Wishlist\Block\Customer\Wishlist\Item\Options'
        );
        $this->assertEmpty($block->getTemplate());
        $product = new \Magento\Framework\DataObject(['type_id' => 'test']);
        $item = new \Magento\Framework\DataObject(['product' => $product]);
        $block->setItem($item);
        $this->assertNotEmpty($block->getTemplate());
        $block->setTemplate('template');
        $this->assertEquals('template', $block->getTemplate());
    }
}
