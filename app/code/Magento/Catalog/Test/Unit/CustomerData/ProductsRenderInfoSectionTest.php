<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\CustomerData;

use Magento\Catalog\Api\Data\ProductFrontendActionInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Api\Data\ProductRenderSearchResultsInterface;
use Magento\Catalog\CustomerData\ProductsRenderInfoSection;
use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Catalog\Model\ProductRenderList;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\Hydrator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductsRenderInfoSectionTest extends TestCase
{
    /** @var ProductsRenderInfoSection */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var StoreManager|MockObject */
    protected $storeManagerMock;

    /** @var SearchCriteriaBuilder|MockObject */
    protected $searchCriteriaBuilderMock;

    /** @var FilterBuilder|MockObject */
    protected $filterBuilderMock;

    /** @var ProductRenderList|MockObject */
    protected $productRenderRepositoryMock;

    /** @var MockObject */
    protected $synchronizerMock;

    /** @var Hydrator|MockObject */
    protected $hydratorMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManager::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->productRenderRepositoryMock = $this->createMock(ProductRenderList::class);
        $this->synchronizerMock = $this->createMock(Synchronizer::class);
        $this->hydratorMock = $this->createMock(Hydrator::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            ProductsRenderInfoSection::class,
            [
                'storeManager' => $this->storeManagerMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'productRenderList' => $this->productRenderRepositoryMock,
                'actionsSynchronizer' => $this->synchronizerMock,
                'hydrator' => $this->hydratorMock
            ]
        );
    }

    private function prepareProductIds()
    {
        $actionFirst = $this->createMock(ProductFrontendActionInterface::class);
        $actionSecond = $this->createMock(ProductFrontendActionInterface::class);
        $actions = [$actionFirst, $actionSecond];
        $this->synchronizerMock->expects($this->once())
            ->method('getAllActions')
            ->willReturn($actions);
        $actionFirst->method('getProductId')->willReturn(1);
        $actionSecond->method('getProductId')->willReturn(2);
    }

    public function testGetSectionData()
    {
        $productRender = $this->createMock(ProductRenderInterface::class);
        $searchResult = $this->createMock(ProductRenderSearchResultsInterface::class);

        $store = $this->createPartialMock(Store::class, ['getId', 'getCurrentCurrencyCode']);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn(3);
        $store->expects($this->once())
            ->method('getCurrentCurrencyCode')
            ->willReturn('UAH');
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $filterMock = $this->createMock(Filter::class);
        $this->filterBuilderMock
            ->expects($this->once())
            ->method('setField')
            ->with('entity_id')
            ->willReturnSelf();
        $this->prepareProductIds();
        $this->filterBuilderMock
            ->expects($this->once())
            ->method('setValue')
            ->with([1, 2])
            ->willReturnSelf();
        $this->filterBuilderMock
            ->expects($this->once())
            ->method('setConditionType')
            ->with('in')
            ->willReturnSelf();
        $this->filterBuilderMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($filterMock);
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('addFilters')
            ->with([$filterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->productRenderRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteria, 3, 'UAH')
            ->willReturn($searchResult);
        $searchResult->method('getItems')->willReturn([$productRender]);
        $this->hydratorMock->expects($this->once())
            ->method('extract')
            ->with($productRender)
            ->willReturn(
                [
                    'name' => 'One',
                    'price_info' => [
                        'final_price' => 12
                    ]
                ]
            );

        $productRender->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->assertEquals(
            [
                1 => [
                    'name' => 'One',
                    'price_info' => [
                        'final_price' => 12
                    ]
                ]
            ],
            $this->model->getSectionData()
        );
    }
}
