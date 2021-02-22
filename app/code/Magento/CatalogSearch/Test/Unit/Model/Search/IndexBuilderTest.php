<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\CatalogSearch\Model\Search\TableMapper;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\CatalogSearch\Model\Search\FilterMapper\DimensionsProcessor;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainerBuilder;
use Magento\CatalogSearch\Model\Search\BaseSelectStrategy\StrategyMapper as BaseSelectStrategyMapper;
use Magento\CatalogSearch\Model\Search\BaseSelectStrategy\BaseSelectStrategyInterface;
use Magento\CatalogSearch\Model\Search\FilterMapper\FilterMapper;
use Magento\CatalogSearch\Model\Search\IndexBuilder;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\DB\Select;

/**
 * Test for \Magento\CatalogSearch\Model\Search\IndexBuilder
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class IndexBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DimensionsProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dimensionsProcessor;

    /**
     * @var SelectContainerBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $selectContainerBuilder;

    /**
     * @var BaseSelectStrategyMapper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $baseSelectStrategyMapper;

    /**
     * @var FilterMapper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterMapper;

    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp(): void
    {
        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();

        $conditionManager = $this->getMockBuilder(ConditionManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $scopeResolver = $this->getMockBuilder(IndexScopeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tableMapper = $this->getMockBuilder(TableMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dimensionScopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dimensionsProcessor = $this->getMockBuilder(DimensionsProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectContainerBuilder = $this->getMockBuilder(SelectContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->baseSelectStrategyMapper = $this->getMockBuilder(BaseSelectStrategyMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterMapper = $this->getMockBuilder(FilterMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->indexBuilder = $objectManagerHelper->getObject(
            IndexBuilder::class,
            [
                'resource' => $resource,
                'config' => $config,
                'storeManager' => $storeManager,
                'conditionManager' => $conditionManager,
                'scopeResolver' => $scopeResolver,
                'tableMapper' => $tableMapper,
                'dimensionScopeResolver' => $dimensionScopeResolver,
                'dimensionsProcessor' => $this->dimensionsProcessor,
                'selectContainerBuilder' => $this->selectContainerBuilder,
                'baseSelectStrategyMapper' => $this->baseSelectStrategyMapper,
                'filterMapper' => $this->filterMapper,
            ]
        );
    }

    public function testBuilder()
    {
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $selectContainer = $this->getMockBuilder(SelectContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSelect'])
            ->getMock();

        $selectContainer
            ->method('getSelect')
            ->willReturn($select);

        $baseSelectStrategyInterface = $this->getMockBuilder(BaseSelectStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->selectContainerBuilder
            ->method('buildByRequest')
            ->with($request)
            ->willReturn($selectContainer);

        $this->baseSelectStrategyMapper
            ->method('mapSelectContainerToStrategy')
            ->with($selectContainer)
            ->willReturn($baseSelectStrategyInterface);

        $baseSelectStrategyInterface
            ->method('createBaseSelect')
            ->with($selectContainer)
            ->willReturn($selectContainer);

        $this->filterMapper
            ->method('applyFilters')
            ->with($selectContainer)
            ->willReturn($selectContainer);

        $this->dimensionsProcessor
            ->method('processDimensions')
            ->with($selectContainer)
            ->willReturn($selectContainer);

        $this->assertSame(
            $select,
            $this->indexBuilder->build($request),
            'Build must return Select instance'
        );
    }
}
