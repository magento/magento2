<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\AttributeGroupAttributeSetIdFilter;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection;
use Magento\Framework\Api\Filter;

class AttributeGroupAttributeSetIdFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeGroupAttributeSetIdFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->filter = new AttributeGroupAttributeSetIdFilter();
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

        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('setAttributeSetFilter')
            ->with($filterValue)
            ->willReturnSelf();

        $this->assertTrue($this->filter->apply($filterMock, $collectionMock));
    }
}
