<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Model\Recommendations;

use Magento\AdvancedSearch\Model\Recommendations\DataProvider;
use Magento\AdvancedSearch\Model\ResourceModel\Recommendations;
use Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory;
use Magento\Catalog\Model\Layer as SearchLayer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Search\Model\QueryInterface;
use Magento\Search\Model\QueryResult;
use Magento\Search\Model\QueryResultFactory;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\AdvancedSearch\Model\Recommendations\DataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var DataProvider;
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Resolver|MockObject
     */
    private $layerResolverMock;

    /**
     * @var SearchLayer|MockObject
     */
    private $searchLayerMock;

    /**
     * @var RecommendationsFactory|MockObject
     */
    private $recommendationsFactoryMock;

    /**
     * @var Recommendations|MockObject
     */
    private $recommendationsMock;

    /**
     * @var Resolver|MockObject
     */
    private $queryResultFactoryMock;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->layerResolverMock = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $this->searchLayerMock = $this->createMock(SearchLayer::class);

        $this->layerResolverMock->expects($this->any())
            ->method('get')
            ->willReturn($this->searchLayerMock);

        $this->recommendationsFactoryMock = $this->getMockBuilder(RecommendationsFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->recommendationsMock = $this->createMock(Recommendations::class);

        $this->queryResultFactoryMock = $this->getMockBuilder(QueryResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            DataProvider::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'layerResolver' => $this->layerResolverMock,
                'recommendationsFactory' => $this->recommendationsFactoryMock,
                'queryResultFactory' => $this->queryResultFactoryMock
            ]
        );
    }

    /**
     * Test testGetItems() when Search Recommendations disabled.
     *
     * @return void
     */
    public function testGetItemsWhenDisabledSearchRecommendations(): void
    {
        $isEnabledSearchRecommendations = false;

        /** @var QueryInterface $queryInterfaceMock */
        $queryInterfaceMock = $this->getMockForAbstractClass(QueryInterface::class);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with('catalog/search/search_recommendations_enabled', ScopeInterface::SCOPE_STORE)
            ->willReturn($isEnabledSearchRecommendations);

        $result = $this->model->getItems($queryInterfaceMock);
        $this->assertEquals([], $result);
    }

    /**
     * Test testGetItems() when Search Recommendations enabled.
     *
     * @return void
     */
    public function testGetItemsWhenEnabledSearchRecommendations(): void
    {
        $storeId = 1;
        $searchRecommendationsCountConfig = 2;
        $isEnabledSearchRecommendations = true;
        $queryText = 'test';

        /** @var QueryInterface $queryInterfaceMock */
        $queryInterfaceMock = $this->getMockForAbstractClass(QueryInterface::class);
        $queryInterfaceMock->expects($this->any())->method('getQueryText')->willReturn($queryText);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with('catalog/search/search_recommendations_enabled', ScopeInterface::SCOPE_STORE)
            ->willReturn($isEnabledSearchRecommendations);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with('catalog/search/search_recommendations_count', ScopeInterface::SCOPE_STORE)
            ->willReturn($searchRecommendationsCountConfig);

        $productCollectionMock = $this->createMock(ProductCollection::class);
        $productCollectionMock->expects($this->any())->method('getStoreId')->willReturn($storeId);

        $this->searchLayerMock->expects($this->any())->method('getProductCollection')
            ->willReturn($productCollectionMock);

        $this->recommendationsFactoryMock->expects($this->any())->method('create')
            ->willReturn($this->recommendationsMock);

        $this->recommendationsMock->expects($this->any())->method('getRecommendationsByQuery')
            ->with($queryText, ['store_id' => $storeId], $searchRecommendationsCountConfig)
            ->willReturn(
                [
                    [
                        'query_text' => 'a',
                        'num_results' => 3
                    ],
                    [
                        'query_text' => 'b',
                        'num_results' => 2
                    ]
                ]
            );
        $queryResultMock = $this->createMock(QueryResult::class);
        $this->queryResultFactoryMock->expects($this->any())->method('create')->willReturn($queryResultMock);

        $result = $this->model->getItems($queryInterfaceMock);
        $this->assertCount(2, $result);
    }
}
