<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product;

class ListTest extends \PHPUnit\Framework\TestCase
{
    public function testGetMode()
    {
        $childBlock = new \Magento\Framework\DataObject();

        $block = $this->createPartialMock(\Magento\Catalog\Block\Product\ListProduct::class, ['getChildBlock']);
        $block->expects(
            $this->atLeastOnce()
        )->method(
            'getChildBlock'
        )->with(
            'toolbar'
        )->will(
            $this->returnValue($childBlock)
        );

        $expectedMode = 'a mode';
        $this->assertNotEquals($expectedMode, $block->getMode());
        $childBlock->setCurrentMode($expectedMode);
        $this->assertEquals($expectedMode, $block->getMode());
    }
}
