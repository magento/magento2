<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Wishlist\Block\Customer\Wishlist\Item\Options.
 */
namespace Magento\Wishlist\Block\Customer\Wishlist\Item;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    public function testGetTemplate()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Wishlist\Block\Customer\Wishlist\Item\Options::class
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
