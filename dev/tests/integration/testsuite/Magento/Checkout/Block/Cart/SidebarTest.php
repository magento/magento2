<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Cart\Sidebar
     */
    protected $_block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Checkout\Block\Cart\Sidebar'
        );
        $this->_block->addChild('renderer.list', '\Magento\Framework\View\Element\RendererList');
        $this->_block->getChildBlock(
            'renderer.list'
        )->addChild(
            'default',
            '\Magento\Checkout\Block\Cart\Item\Renderer',
            ['template' => 'cart/item/default.phtml']
        );
    }

    public function testGetCacheKeyInfo()
    {
        $this->assertEquals(
            [
                'BLOCK_TPL',
                'default',
                $this->_block->getTemplateFile(),
                'template' => null,
                'item_renders' => 'default|Magento\Checkout\Block\Cart\Item\Renderer|cart/item/default.phtml',
            ],
            $this->_block->getCacheKeyInfo()
        );
    }
}
