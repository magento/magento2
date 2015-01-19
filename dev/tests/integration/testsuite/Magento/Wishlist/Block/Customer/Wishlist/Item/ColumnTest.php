<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Block\Customer\Wishlist\Item;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout = null;

    /**
     * @var \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
     */
    protected $_block = null;

    protected function setUp()
    {
        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $this->_block = $this->_layout->addBlock('Magento\Wishlist\Block\Customer\Wishlist\Item\Column', 'test');
        $this->_layout->addBlock('Magento\Framework\View\Element\Text', 'child', 'test');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testToHtml()
    {
        $item = new \StdClass();
        $this->_block->setItem($item);
        $this->_block->toHtml();
        $this->assertSame($item, $this->_layout->getBlock('child')->getItem());
    }

    public function testGetJs()
    {
        $expected = uniqid();
        $this->_layout->getBlock('child')->setJs($expected);
        $this->assertEquals($expected, $this->_block->getJs());
    }
}
