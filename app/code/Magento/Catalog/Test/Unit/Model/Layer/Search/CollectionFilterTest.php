<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Search;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Catalog\Model\Layer\Search\CollectionFilter
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->visibilityMock = $this->createMock(\Magento\Catalog\Model\Product\Visibility::class);
        $this->catalogConfigMock = $this->createMock(\Magento\Catalog\Model\Config::class);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Layer\Search\CollectionFilter::class,
            [
                'catalogConfig' => $this->catalogConfigMock,
                'storeManager' => $this->storeManagerMock,
                'productVisibility' => $this->visibilityMock
            ]
        );
    }

    /**
     * @covers \Magento\Catalog\Model\Layer\Search\CollectionFilter::filter
     * @covers \Magento\Catalog\Model\Layer\Search\CollectionFilter::__construct
     */
    public function testFilter()
    {
        $collectionMock = $this->createPartialMock(\Magento\Catalog\Model\ResourceModel\Product\Collection::class, [
                'addAttributeToSelect', 'setStore', 'addMinimalPrice', 'addFinalPrice',
                'addTaxPercents', 'addStoreFilter', 'addUrlRewrite', 'setVisibility'
            ]);
        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);

        $this->catalogConfigMock->expects($this->once())->method('getProductAttributes');
        $this->visibilityMock->expects($this->once())->method('getVisibleInSearchIds');
        $this->storeManagerMock->expects($this->once())->method('getStore');

        $collectionMock->expects($this->once())->method('addAttributeToSelect')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('setStore')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addMinimalPrice')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addFinalPrice')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addTaxPercents')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addStoreFilter')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('addUrlRewrite')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('setVisibility')->willReturn($collectionMock);

        $this->model->filter($collectionMock, $categoryMock);
    }
}
