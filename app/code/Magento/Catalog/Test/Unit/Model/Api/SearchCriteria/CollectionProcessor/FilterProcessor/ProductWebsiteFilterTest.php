<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\ProductWebsiteFilter;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Filter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductWebsiteFilterTest extends TestCase
{
    /** @var ProductWebsiteFilter */
    private $model;

    protected function setUp(): void
    {
        $this->model = new ProductWebsiteFilter();
    }

    public function testApply()
    {
        /** @var Filter|MockObject $filterMock */
        $filterMock = $this->createMock(Filter::class);

        /** @var Collection|MockObject $collectionMock */
        $collectionMock = $this->createMock(Collection::class);

        $filterMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1,2');

        $collectionMock->expects($this->once())
            ->method('addWebsiteFilter')
            ->with(['1', '2']);

        $this->assertTrue($this->model->apply($filterMock, $collectionMock));
    }
}
