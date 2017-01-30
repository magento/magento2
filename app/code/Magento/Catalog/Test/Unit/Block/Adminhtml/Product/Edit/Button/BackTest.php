<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Back;

/**
 * Class BackTest
 */
class BackTest extends GenericTest
{
    public function testGetButtonData()
    {
        $this->contextMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/', [])
            ->willReturn('/');

        $this->assertEquals(
            [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';", '/'),
                'class' => 'back',
                'sort_order' => 10
            ],
            $this->getModel(Back::class)->getButtonData()
        );
    }
}
