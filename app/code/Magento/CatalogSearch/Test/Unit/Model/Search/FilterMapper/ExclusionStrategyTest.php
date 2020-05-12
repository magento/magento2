<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\CatalogSearch\Model\Search\FilterMapper\ExclusionStrategy;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 */
class ExclusionStrategyTest extends TestCase
{
    /**
     * @var ExclusionStrategy
     */
    private $model;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    protected function setUp(): void
    {
        $resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $adapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($adapterMock);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $aliasResolverMock = $this->createMock(AliasResolver::class);

        $indexScopeResolverMock = $this->createMock(
            IndexScopeResolverInterface::class
        );
        $tableResolverMock = $this->createMock(
            IndexScopeResolver::class
        );
        $dimensionMock = $this->createMock(Dimension::class);
        $dimensionFactoryMock = $this->createMock(DimensionFactory::class);
        $dimensionFactoryMock->method('create')->willReturn($dimensionMock);
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->method('getId')->willReturn(1);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $indexScopeResolverMock->method('resolve')->willReturn('catalog_product_index_price');
        $httpContextMock = $this->createMock(Context::class);
        $httpContextMock->method('getValue')->willReturn(1);

        $this->model = new ExclusionStrategy(
            $resourceConnectionMock,
            $this->storeManagerMock,
            $aliasResolverMock,
            $tableResolverMock,
            $dimensionFactoryMock,
            $indexScopeResolverMock,
            $httpContextMock
        );
    }

    public function testApplyUsesFrontendPriceIndexerTableIfAttributeCodeIsPrice()
    {
        $attributeCode = 'price';
        $websiteId = 1;
        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('joinInner')->willReturnSelf();
        $selectMock->expects($this->any())->method('getPart')->willReturn([]);

        $searchFilterMock = $this->createMock(Term::class);
        $searchFilterMock->expects($this->any())->method('getField')->willReturn($attributeCode);

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->any())->method('getId')->willReturn($websiteId);
        $this->storeManagerMock->expects($this->any())->method('getWebsite')->willReturn($websiteMock);

        $this->assertTrue($this->model->apply($searchFilterMock, $selectMock));
    }
}
