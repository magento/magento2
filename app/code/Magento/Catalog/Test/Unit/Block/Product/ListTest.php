<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

class ListTest extends TestCase
{
    public function testGetMode()
    {
        $childBlock = new DataObject();

        $block = $this->createPartialMock(ListProduct::class, ['getChildBlock']);
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
