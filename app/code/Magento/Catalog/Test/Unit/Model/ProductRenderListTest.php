<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRenderFactory;
use Magento\Catalog\Model\ProductRenderList;
use Magento\Catalog\Model\ProductRenderSearchResultsFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorComposite;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Data\CollectionModifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRenderListTest extends TestCase
{
    /** @var \Magento\Catalog\Model\ProductRenderRepository */
    private $model;

    /** @var MockObject */
    private $collectionFactoryMock;

    /** @var MockObject */
    private $collectionProcessorMock;

    /** @var MockObject */
    private $productRenderCollectorCompositeMock;

    /** @var MockObject */
    private $productRenderSearchResultsFactoryMock;

    /** @var ProductRenderFactory|MockObject */
    private $productRenderFactoryMock;

    /** @var Config|MockObject */
    private $configMock;

    /** @var Visibility|MockObject */
    private $productVisibility;

    /** @var CollectionModifier|MockObject */
    private $collectionModifier;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this
            ->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessorMock = $this
            ->getMockBuilder(CollectionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRenderCollectorCompositeMock = $this
            ->getMockBuilder(ProductRenderCollectorComposite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRenderSearchResultsFactoryMock = $this
            ->getMockBuilder(ProductRenderSearchResultsFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRenderFactoryMock = $this
            ->getMockBuilder(ProductRenderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock->expects($this->once())
            ->method('getProductAttributes')
            ->willReturn([]);
        $this->productVisibility = $this->getMockBuilder(Visibility::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionModifier = $this->getMockBuilder(CollectionModifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new ProductRenderList(
            $this->collectionFactoryMock,
            $this->collectionProcessorMock,
            $this->productRenderCollectorCompositeMock,
            $this->productRenderSearchResultsFactoryMock,
            $this->productRenderFactoryMock,
            $this->configMock,
            $this->collectionModifier,
            ['msrp_price']
        );
    }

    public function testGetList()
    {
        $storeId = 1;
        $currencyCode = 'USD';

        $product = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $iterator = new \IteratorIterator(new \ArrayIterator([$product]));
        $productRender = $this->getMockBuilder(ProductRenderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchResult = $this->getMockBuilder(SearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productCollection);
        $productCollection->expects($this->once())
            ->method('addAttributeToSelect')
            ->with(['msrp_price'])
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('addMinimalPrice')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('addFinalPrice')
            ->willReturnSelf();
        $productCollection->expects($this->once())
            ->method('addTaxPercents')
            ->willReturnSelf();
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $productCollection);
        $productCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);
        $this->collectionModifier->expects($this->once())
            ->method('apply')
            ->with($productCollection);
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $productCollection);
        $product->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productRenderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($productRender);
        $productRender->expects($this->once())
            ->method('setStoreId')
            ->with(1);
        $productRender->expects($this->once())
            ->method('setCurrencyCode')
            ->with($currencyCode);
        $this->productRenderCollectorCompositeMock->expects($this->once())
            ->method('collect')
            ->with($product, $productRender);
        $this->productRenderSearchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResult);
        $searchResult->expects($this->once())
            ->method('setItems')
            ->with([
                1 => $productRender
            ]);
        $searchResult->expects($this->once())
            ->method('setTotalCount')
            ->with(1);
        $searchResult->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);

        $this->assertEquals($searchResult, $this->model->getList($searchCriteria, $storeId, $currencyCode));
    }
}
