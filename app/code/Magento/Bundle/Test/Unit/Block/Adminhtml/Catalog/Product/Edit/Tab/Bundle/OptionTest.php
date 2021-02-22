<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

class OptionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetAddButtonId()
    {
        $button = new \Magento\Framework\DataObject();

        $itemsBlock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getChildBlock']);
        $itemsBlock->expects(
            $this->atLeastOnce()
        )->method(
            'getChildBlock'
        )->with(
            'add_button'
        )->willReturn(
            $button
        );

        $layout = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getBlock']);
        $layout->expects(
            $this->atLeastOnce()
        )->method(
            'getBlock'
        )->with(
            'admin.product.bundle.items'
        )->willReturn(
            $itemsBlock
        );

        $block = $this->createPartialMock(
            \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option::class,
            ['getLayout']
        );
        $block->expects($this->atLeastOnce())->method('getLayout')->willReturn($layout);

        $this->assertNotEquals(42, $block->getAddButtonId());
        $button->setId(42);
        $this->assertEquals(42, $block->getAddButtonId());
    }
}
