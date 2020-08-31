<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Layer\Category\CollectionFilter;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionFilterTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $visibilityMock;

    /**
     * @var MockObject
     */
    protected $catalogConfigMock;

    /**
     * @var CollectionFilter
     */
    protected $model;

    protected function setUp(): void
    {
        $this->visibilityMock = $this->createMock(Visibility::class);
        $this->catalogConfigMock = $this->createMock(Config::class);
        $this->model = new CollectionFilter($this->visibilityMock, $this->catalogConfigMock);
    }

    /**
     * @covers \Magento\Catalog\Model\Layer\Category\CollectionFilter::filter
     * @covers \Magento\Catalog\Model\Layer\Category\CollectionFilter::__construct
     */
    public function testFilter()
    {
        $collectionMock = $this->createMock(Collection::class);

        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())->method('getId');

        $this->catalogConfigMock->expects($this->once())->method('getProductAttributes');
        $this->visibilityMock->expects($this->once())->method('getVisibleInCatalogIds');

        $collectionMock->expects($this->once())->method('addAttributeToSelect')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addMinimalPrice')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addFinalPrice')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addTaxPercents')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addUrlRewrite')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('setVisibility')->willReturn($collectionMock);

        $this->model->filter($collectionMock, $categoryMock);
    }
}
