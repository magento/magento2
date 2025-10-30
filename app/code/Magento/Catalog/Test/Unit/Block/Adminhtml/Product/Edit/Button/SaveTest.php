<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Save;

class SaveTest extends GenericTest
{
    public function testGetButtonData()
    {
        $this->productMock->expects($this->once())
            ->method('isReadonly')
            ->willReturn(false);

        $this->assertNotEmpty($this->getModel(Save::class)->getButtonData());
    }

    public function testGetButtonDataToBeEmpty()
    {
        $this->productMock->expects($this->once())
            ->method('isReadonly')
            ->willReturn(true);

        $this->assertSame([], $this->getModel(Save::class)->getButtonData());
    }
}
