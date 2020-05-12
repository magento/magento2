<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

use Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    public function testGetAddButtonId()
    {
        $button = new DataObject();

        $itemsBlock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getChildBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemsBlock->expects(
            $this->atLeastOnce()
        )->method(
            'getChildBlock'
        )->with(
            'add_button'
        )->willReturn(
            $button
        );

        $layout = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getBlock'])
            ->disableOriginalConstructor()
            ->getMock();
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
            Option::class,
            ['getLayout']
        );
        $block->expects($this->atLeastOnce())->method('getLayout')->willReturn($layout);

        $this->assertNotEquals(42, $block->getAddButtonId());
        $button->setId(42);
        $this->assertEquals(42, $block->getAddButtonId());
    }
}
