<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Category;

use \Magento\Catalog\Model\Layer\Category\CollectionFilter;

class CollectionFilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $visibilityMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $catalogConfigMock;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\CollectionFilter
     */
    protected $model;

    protected function setUp(): void
    {
        $this->visibilityMock = $this->createMock(\Magento\Catalog\Model\Product\Visibility::class);
        $this->catalogConfigMock = $this->createMock(\Magento\Catalog\Model\Config::class);
        $this->model = new CollectionFilter($this->visibilityMock, $this->catalogConfigMock);
    }

    /**
     * @covers \Magento\Catalog\Model\Layer\Category\CollectionFilter::filter
     * @covers \Magento\Catalog\Model\Layer\Category\CollectionFilter::__construct
     */
    public function testFilter()
    {
        $collectionMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);

        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);
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
