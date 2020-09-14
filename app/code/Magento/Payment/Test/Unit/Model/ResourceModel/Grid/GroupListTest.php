<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\ResourceModel\Grid;

use Magento\Payment\Helper\Data;
use Magento\Payment\Model\ResourceModel\Grid\GroupList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupListTest extends TestCase
{
    /**
     * @var \Magento\Payment\Model\ResourceModel\Grid\GroupsList
     */
    protected $groupArrayModel;

    /**
     * @var MockObject
     */
    protected $helperMock;

    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->groupArrayModel = new GroupList($this->helperMock);
    }

    public function testToOptionArray()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('getPaymentMethodList')
            ->with(true, true, true)
            ->willReturn(['group data']);
        $this->assertEquals(['group data'], $this->groupArrayModel->toOptionArray());
    }
}
