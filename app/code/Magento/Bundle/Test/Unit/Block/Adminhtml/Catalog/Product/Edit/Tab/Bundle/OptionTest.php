<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle;

use Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testGetAddButtonId()
    {
        $button = new DataObject();
        $button->setId(42);

        $itemsBlock = $this->createMock(AbstractBlock::class);
        $itemsBlock->method('getChildBlock')->with('add_button')->willReturn($button);

        $layout = $this->createMock(LayoutInterface::class);
        $layout->method('getBlock')->with('admin.product.bundle.items')->willReturn($itemsBlock);

        $block = $this->createPartialMock(
            Option::class,
            ['getLayout']
        );
        $block->expects($this->atLeastOnce())->method('getLayout')->willReturn($layout);

        // Test that the button ID is correctly retrieved
        $this->assertEquals(42, $block->getAddButtonId());
    }
}
