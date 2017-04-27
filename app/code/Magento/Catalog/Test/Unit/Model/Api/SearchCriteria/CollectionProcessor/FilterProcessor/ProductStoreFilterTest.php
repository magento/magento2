<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\ProductStoreFilter;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Filter;

class ProductStoreFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var ProductStoreFilter */
    private $model;

    protected function setUp()
    {
        $this->model = new ProductStoreFilter();
    }

    public function testApply()
    {
        /** @var Filter|\PHPUnit_Framework_MockObject_MockObject $filterMock */
        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filterMock->expects($this->exactly(2))
            ->method('getConditionType')
            ->willReturn('condition');
        $filterMock->expects($this->once())
            ->method('getValue')
            ->willReturn('value');

        $collectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with(['condition' => ['value']]);

        $this->assertTrue($this->model->apply($filterMock, $collectionMock));
    }

    public function testApplyWithoutCondition()
    {
        /** @var Filter|\PHPUnit_Framework_MockObject_MockObject $filterMock */
        $filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filterMock->expects($this->once())
            ->method('getConditionType')
            ->willReturn(null);
        $filterMock->expects($this->once())
            ->method('getValue')
            ->willReturn('value');

        $collectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with(['eq' => ['value']]);

        $this->assertTrue($this->model->apply($filterMock, $collectionMock));
    }
}
