<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Back;

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
