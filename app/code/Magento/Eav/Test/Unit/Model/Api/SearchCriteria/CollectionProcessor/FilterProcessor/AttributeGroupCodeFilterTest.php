<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\AttributeGroupCodeFilter;
use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb;
use PHPUnit\Framework\TestCase;

class AttributeGroupCodeFilterTest extends TestCase
{
    /**
     * @var AttributeGroupCodeFilter
     */
    private $filter;

    protected function setUp(): void
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
            ->onlyMethods(['addFilter'])
            ->getMockForAbstractClass();
        $collectionMock->expects($this->once())
            ->method('addFilter')
            ->with('attribute_group_code', $filterValue)
            ->willReturnSelf();

        $this->assertTrue($this->filter->apply($filterMock, $collectionMock));
    }
}
