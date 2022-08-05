<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal
 */
class DecimalTest extends TestCase
{
    /** @var  FilterInterface|MockObject */
    private $filter;

    /** @var  Decimal|MockObject */
    private $resource;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal
     */
    private $target;

    protected function setUp(): void
    {
        $this->filter = $this->getMockBuilder(FilterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(Decimal::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMinMax', 'getCount'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal::class,
            [
                'resource' => $this->resource,
            ]
        );
    }

    public function testGetMaxValue()
    {
        $this->resource->expects($this->once())
            ->method('getMinMax')
            ->with($this->filter)
            ->willReturn([10, 20]);
        $max = $this->target->getMaxValue($this->filter);
        $this->assertSame(20, $max);
    }

    public function testGetMinValue()
    {
        $this->resource->expects($this->once())
            ->method('getMinMax')
            ->with($this->filter)
            ->willReturn([50, 220]);
        $min = $this->target->getMinValue($this->filter);
        $this->assertSame(50, $min);
    }

    public function testGetRangeItemCounts()
    {
        $range = 100500;
        $this->resource->expects($this->once())
            ->method('getCount')
            ->with($this->filter, $range)
            ->willReturn(350);
        $this->assertSame(350, $this->target->getRangeItemCounts($range, $this->filter));
    }

    public function testGetRange()
    {
        $this->resource->expects($this->once())
            ->method('getMinMax')
            ->with($this->filter)
            ->willReturn([74, 147]);
        $range = $this->target->getRange($this->filter);
        $this->assertSame(10, $range);
    }
}
