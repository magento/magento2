<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Cms\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\PageStoreFilter;

class PageStoreFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageStoreFilter
     */
    private $filter;

    protected function setUp()
    {
        $this->filter = new PageStoreFilter();
    }

    public function testApply()
    {
        $filterValue = 'filter_value';

        $filterMock = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock->expects($this->once())
            ->method('getValue')
            ->willReturn($filterValue);

        $collectionMock = $this->getMockBuilder(\Magento\Cms\Model\ResourceModel\Page\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with($filterValue, false)
            ->willReturnSelf();

        $this->assertTrue($this->filter->apply($filterMock, $collectionMock));
    }
}
