<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Search\FilterMapper\ExclusionStrategy;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExclusionStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ExclusionStrategy
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $aliasResolverMock;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->adapterMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->any())->method('getConnection')->willReturn($this->adapterMock);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->aliasResolverMock = $this->createMock(AliasResolver::class);

        $this->indexScopeResolverMock = $this->createMock(
            \Magento\Framework\Search\Request\IndexScopeResolverInterface::class
        );
        $this->tableResolverMock = $this->createMock(
            \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver::class
        );
        $this->dimensionMock = $this->createMock(\Magento\Framework\Indexer\Dimension::class);
        $this->dimensionFactoryMock = $this->createMock(\Magento\Framework\Indexer\DimensionFactory::class);
        $this->dimensionFactoryMock->method('create')->willReturn($this->dimensionMock);
        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeMock->method('getId')->willReturn(1);
        $storeMock->method('getWebsiteId')->willReturn(1);
        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $this->indexScopeResolverMock->method('resolve')->willReturn('catalog_product_index_price');
        $this->httpContextMock = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->httpContextMock->method('getValue')->willReturn(1);

        $this->model = new ExclusionStrategy(
            $this->resourceConnectionMock,
            $this->storeManagerMock,
            $this->aliasResolverMock,
            $this->tableResolverMock,
            $this->dimensionFactoryMock,
            $this->indexScopeResolverMock,
            $this->httpContextMock
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

        $websiteMock = $this->createMock(WebsiteInterface::class);
        $websiteMock->expects($this->any())->method('getId')->willReturn($websiteId);
        $this->storeManagerMock->expects($this->any())->method('getWebsite')->willReturn($websiteMock);

        $this->assertTrue($this->model->apply($searchFilterMock, $selectMock));
    }
}
