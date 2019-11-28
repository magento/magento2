<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Model\Recommendations;

use Magento\AdvancedSearch\Model\Recommendations\DataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\AdvancedSearch\Model\ResourceModel\Recommendations;
use Magento\AdvancedSearch\Model\ResourceModel\RecommendationsFactory;
use Magento\Search\Model\QueryResult;
use Magento\Search\Model\QueryResultFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Layer as SearchLayer;
use Magento\Store\Model\ScopeInterface;
use Magento\Search\Model\QueryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * Class \Magento\AdvancedSearch\Test\Unit\Model\Recommendations\DataProviderTest
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataProvider;
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Resolver
     */
    private $layerResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SearchLayer
     */
    private $searchLayerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RecommendationsFactory
     */
    private $recommendationsFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Recommendations
     */
    private $recommendationsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Resolver
     */
    private $queryResultFactory;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->layerResolverMock = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->searchLayerMock = $this->createMock(SearchLayer::class);

        $this->layerResolverMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->searchLayerMock));

        $this->recommendationsFactoryMock = $this->getMockBuilder(RecommendationsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->recommendationsMock = $this->createMock(Recommendations::class);

        $this->queryResultFactory = $this->getMockBuilder(QueryResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            DataProvider::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'layerResolver' => $this->layerResolverMock,
                'recommendationsFactory' => $this->recommendationsFactoryMock,
                'queryResultFactory' => $this->queryResultFactory
            ]
        );
    }

    /**
     * Test testGetItems() when Search Recommendations disabled.
     *
     * @return void
     */
    public function testGetItemsWhenDisabledSearchRecommendations()
    {
        $isEnabledSearchRecommendations = false;

        /** @var $queryInterfaceMock QueryInterface */
        $queryInterfaceMock = $this->createMock(QueryInterface::class);

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
    public function testGetItemsWhenEnabledSearchRecommendations()
    {
        $storeId = 1;
        $searchRecommendationsCountConfig = 2;
        $isEnabledSearchRecommendations = true;
        $queryText = 'test';

        /** @var $queryInterfaceMock QueryInterface */
        $queryInterfaceMock = $this->createMock(QueryInterface::class);
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
        $this->queryResultFactory->expects($this->any())->method('create')->willReturn($queryResultMock);

        $result = $this->model->getItems($queryInterfaceMock);
        $this->assertEquals(2, count($result));
    }
}
