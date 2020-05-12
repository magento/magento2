<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Cms\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\PageStoreFilter;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Framework\Api\Filter;
use PHPUnit\Framework\TestCase;

class PageStoreFilterTest extends TestCase
{
    /**
     * @var PageStoreFilter
     */
    private $filter;

    protected function setUp(): void
    {
        $this->filter = new PageStoreFilter();
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
            ->method('addStoreFilter')
            ->with($filterValue, false)
            ->willReturnSelf();

        $this->assertTrue($this->filter->apply($filterMock, $collectionMock));
    }
}
