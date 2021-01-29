<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\ResourceModel\Grid;

class TypeListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Payment\Model\ResourceModel\Grid\TypeList
     */
    protected $typesArrayModel;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(\Magento\Payment\Helper\Data::class);
        $this->typesArrayModel = new \Magento\Payment\Model\ResourceModel\Grid\TypeList($this->helperMock);
    }

    public function testToOptionArray()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('getPaymentMethodList')
            ->with(true)
            ->willReturn(['group data']);
        $this->assertEquals(['group data'], $this->typesArrayModel->toOptionArray());
    }
}
