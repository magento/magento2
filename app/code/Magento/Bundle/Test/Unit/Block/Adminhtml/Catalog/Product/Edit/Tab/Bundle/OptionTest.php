<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAddButtonId()
    {
        $button = new \Magento\Framework\DataObject();

        $itemsBlock = $this->getMock('Magento\Framework\DataObject', ['getChildBlock']);
        $itemsBlock->expects(
            $this->atLeastOnce()
        )->method(
            'getChildBlock'
        )->with(
            'add_button'
        )->will(
            $this->returnValue($button)
        );

        $layout = $this->getMock('Magento\Framework\DataObject', ['getBlock']);
        $layout->expects(
            $this->atLeastOnce()
        )->method(
            'getBlock'
        )->with(
            'admin.product.bundle.items'
        )->will(
            $this->returnValue($itemsBlock)
        );

        $block = $this->getMock(
            'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option',
            ['getLayout'],
            [],
            '',
            false
        );
        $block->expects($this->atLeastOnce())->method('getLayout')->will($this->returnValue($layout));

        $this->assertNotEquals(42, $block->getAddButtonId());
        $button->setId(42);
        $this->assertEquals(42, $block->getAddButtonId());
    }
}
