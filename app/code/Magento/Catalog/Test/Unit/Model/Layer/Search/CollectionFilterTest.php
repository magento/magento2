<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Search;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Layer\Search\CollectionFilter;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var CollectionFilter
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->visibilityMock = $this->createMock(Visibility::class);
        $this->catalogConfigMock = $this->createMock(Config::class);

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->model = $objectManager->getObject(
            CollectionFilter::class,
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
        $collectionMock = $this->createPartialMock(Collection::class, [
            'addAttributeToSelect', 'setStore', 'addMinimalPrice', 'addFinalPrice',
            'addTaxPercents', 'addStoreFilter', 'addUrlRewrite', 'setVisibility'
        ]);
        $categoryMock = $this->createMock(Category::class);

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
