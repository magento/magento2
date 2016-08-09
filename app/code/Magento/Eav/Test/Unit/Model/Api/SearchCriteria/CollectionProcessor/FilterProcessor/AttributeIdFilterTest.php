<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\AttributeIdFilter;
use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb;

class AttributeIdFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeIdFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->filter = new AttributeIdFilter();
    }

    public function testApply()
    {
        $filterValue = 'filter_value';

        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock->expects($this->exactly(2))
            ->method('getField')
            ->willReturn(AttributeInterface::ATTRIBUTE_ID);
        $filterMock->expects($this->once())
            ->method('getValue')
            ->willReturn($filterValue);
        $filterMock->expects($this->once())
            ->method('getConditionType')
            ->willReturn(null);

        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter'])
            ->getMockForAbstractClass();
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.' . AttributeInterface::ATTRIBUTE_ID, ['eq' => $filterValue])
            ->willReturnSelf();

        $this->assertTrue($this->filter->apply($filterMock, $collectionMock));
    }

    public function testApplyIdle()
    {
        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock->expects($this->once())
            ->method('getField')
            ->willReturn(null);

        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertFalse($this->filter->apply($filterMock, $collectionMock));
    }
}
