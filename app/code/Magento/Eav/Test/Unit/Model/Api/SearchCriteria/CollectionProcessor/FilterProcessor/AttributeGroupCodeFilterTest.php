<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\AttributeGroupCodeFilter;
use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb;

class AttributeGroupCodeFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeGroupCodeFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->filter = new AttributeGroupCodeFilter();
    }

    public function testApply()
    {
        $filterValue = 'filter_value';

        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock->expects($this->once())
            ->method('getValue')
            ->willReturn($filterValue);

        $collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter'])
            ->getMockForAbstractClass();
        $collectionMock->expects($this->once())
            ->method('addFilter')
            ->with('attribute_group_code', $filterValue)
            ->willReturnSelf();

        $this->assertTrue($this->filter->apply($filterMock, $collectionMock));
    }
}
