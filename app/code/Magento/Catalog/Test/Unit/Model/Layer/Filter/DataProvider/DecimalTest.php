<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal
 */
class DecimalTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Catalog\Model\Layer\Filter\FilterInterface|MockObject */
    private $filter;

    /** @var  \Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal|MockObject */
    private $resource;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal
     */
    private $target;

    protected function setUp()
    {
        $this->filter = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\FilterInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal')
            ->disableOriginalConstructor()
            ->setMethods(['getMinMax', 'getCount'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal',
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
            ->will($this->returnValue([10, 20]));
        $max = $this->target->getMaxValue($this->filter);
        $this->assertSame(20, $max);
    }

    public function testGetMinValue()
    {
        $this->resource->expects($this->once())
            ->method('getMinMax')
            ->with($this->filter)
            ->will($this->returnValue([50, 220]));
        $min = $this->target->getMinValue($this->filter);
        $this->assertSame(50, $min);
    }

    public function testGetRangeItemCounts()
    {
        $range = 100500;
        $this->resource->expects($this->once())
            ->method('getCount')
            ->with($this->filter, $range)
            ->will($this->returnValue(350));
        $this->assertSame(350, $this->target->getRangeItemCounts($range, $this->filter));
    }

    public function testGetRange()
    {
        $this->resource->expects($this->once())
            ->method('getMinMax')
            ->with($this->filter)
            ->will($this->returnValue([74, 147]));
        $range = $this->target->getRange($this->filter);
        $this->assertSame(10, $range);
    }
}
