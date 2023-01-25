<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Items\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultColumnTest extends TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var DefaultColumn
     */
    protected $defaultColumn;

    /**
     * @var Item|MockObject
     */
    protected $itemMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->defaultColumn = $this->objectManagerHelper->getObject(
            DefaultColumn::class
        );
        $this->itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRowTotal', 'getDiscountAmount', 'getBaseRowTotal', 'getBaseDiscountAmount'])
            ->getMock();
    }

    public function testGetTotalAmount()
    {
        $rowTotal = 10;
        $discountAmount = 2;
        $expectedResult = 8;
        $this->itemMock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn($rowTotal);
        $this->itemMock->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn($discountAmount);
        $this->assertEquals($expectedResult, $this->defaultColumn->getTotalAmount($this->itemMock));
    }

    public function testGetBaseTotalAmount()
    {
        $baseRowTotal = 10;
        $baseDiscountAmount = 2;
        $expectedResult = 8;
        $this->itemMock->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($baseRowTotal);
        $this->itemMock->expects($this->once())
            ->method('getBaseDiscountAmount')
            ->willReturn($baseDiscountAmount);
        $this->assertEquals($expectedResult, $this->defaultColumn->getBaseTotalAmount($this->itemMock));
    }
}
