<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogRule\Model\Indexer\CatalogRuleInsertBatchSizeCalculator;
use Magento\CatalogRule\Model\Indexer\DynamicBatchSizeCalculator;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\Indexer\IndexBuilder\ProductLoader;
use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface;
use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\CatalogRule\Model\Indexer\ReindexRuleGroupWebsite;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProduct;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice;
use Magento\CatalogRule\Model\Indexer\ReindexRuleProductsPrice;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor;
use Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\CatalogRule\Model\ResourceModel\Rule\RuleIdProvider;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for Magento\CatalogRule\Model\Indexer\IndexBuilder class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilderTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var ReindexRuleProduct|MockObject
     */
    private $reindexRuleProduct;

    /**
     * @var ReindexRuleGroupWebsite|MockObject
     */
    private $reindexRuleGroupWebsite;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistry;

    /**
     * @var ReindexRuleProductsPrice|MockObject
     */
    private $reindexRuleProductsPrice;

    /**
     * @var IndexBuilder
     */
    private $builder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resource = $this->createMock(ResourceConnection::class);
        $connection = $this->createMock(AdapterInterface::class);
        $this->resource->method('getConnection')->willReturn($connection);
        $this->reindexRuleProduct = $this->createMock(ReindexRuleProduct::class);
        $this->reindexRuleGroupWebsite = $this->createMock(ReindexRuleGroupWebsite::class);
        $this->indexerRegistry = $this->createMock(IndexerRegistry::class);
        $this->reindexRuleProductsPrice = $this->createMock(ReindexRuleProductsPrice::class);
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->builder = new IndexBuilder(
            $this->collectionFactory,
            $this->createMock(PriceCurrencyInterface::class),
            $this->resource,
            $this->createMock(StoreManagerInterface::class),
            $this->createMock(LoggerInterface::class),
            $this->createMock(Config::class),
            $this->createMock(DateTime::class),
            $this->createMock(DateTime\DateTime::class),
            $this->createMock(ProductFactory::class),
            2000,
            $this->createMock(ProductPriceCalculator::class),
            $this->reindexRuleProduct,
            $this->reindexRuleGroupWebsite,
            $this->createMock(RuleProductsSelectBuilder::class),
            $this->createMock(ReindexRuleProductPrice::class),
            $this->createMock(RuleProductPricesPersistor::class),
            $this->createMock(ActiveTableSwitcher::class),
            $this->createMock(ProductLoader::class),
            $this->createMock(IndexerTableSwapperInterface::class),
            $this->createMock(TimezoneInterface::class),
            $this->createMock(ProductCollectionFactory::class),
            $this->indexerRegistry,
            $this->reindexRuleProductsPrice,
            2000,
            $this->createMock(DynamicBatchSizeCalculator::class),
            $this->createMock(CatalogRuleInsertBatchSizeCalculator::class),
            $this->createMock(RuleIdProvider::class),
            $this->createMock(RuleFactory::class),
            $this->createMock(GroupExcludedWebsiteRepositoryInterface::class),
        );
    }

    /**
     * Verifies that each resident rule releases the product filter immediately after processing.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testDoReindexByIdsClearsProductsFilterAfterEachRule(): void
    {
        $ruleIds = [1, 2];
        $ids = [11, 22];
        $filters = [];
        $events = [];
        $rules = [];

        foreach ($ruleIds as $id) {
            $rule = $this->getMockBuilder(Rule::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['setProductsFilter', 'getProductsFilter', 'getId'])
                ->getMock();
            $rule->method('setProductsFilter')->willReturnCallback(
                function (array $value) use (&$filters, &$events, $rule, $id): void {
                    $filters[spl_object_id($rule)] = $value;
                    $events[] = [$id, $value];
                }
            );
            $rule->method('getProductsFilter')->willReturnCallback(
                fn(): array => $filters[spl_object_id($rule)] ?? []
            );
            $rule->method('getId')->willReturn($id);
            $rules[] = $rule;
        }

        $ruleCollection = $this->createMock(Collection::class);
        $ruleCollection->expects($this->once())->method('getItems')->willReturn($rules);
        $ruleCollection->expects($this->once())->method('addFieldToFilter')
            ->with('is_active', 1)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($ruleCollection);

        $this->reindexRuleProduct->expects($this->exactly(2))
            ->method('execute')
            ->willReturnCallback(function (Rule $rule) use (&$filters, &$events, $ids): void {
                $ruleId = $rule->getId();
                $events[] = ['execute', $ruleId];
                $this->assertSame($ids, $filters[spl_object_id($rule)]);
            });

        $connection = $this->resource->getConnection();
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $connection->method('select')->willReturn($select);
        $connection->method('fetchOne')->willReturn(0);
        $this->resource->method('getTableName')->willReturnArgument(0);
        $connection->method('delete');

        $ruleIndexer = $this->createMock(IndexerInterface::class);
        $ruleIndexer->method('isScheduled')->willReturn(true);
        $priceIndexer = $this->createMock(IndexerInterface::class);
        $priceIndexer->method('isScheduled')->willReturn(true);
        $this->indexerRegistry->method('get')->willReturnMap([
            [RuleProductProcessor::INDEXER_ID, $ruleIndexer],
            [\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID, $priceIndexer],
        ]);

        $this->reindexRuleProductsPrice->expects($this->once())->method('execute')->with(2000, $ids);
        $this->reindexRuleGroupWebsite->expects($this->once())->method('execute');

        $this->builder->reindexByIds($ids);

        $this->assertSame(
            [
                [1, $ids],
                ['execute', 1],
                [1, []],
                [2, $ids],
                ['execute', 2],
                [2, []],
            ],
            $events
        );
        foreach ($rules as $rule) {
            $this->assertSame([], $rule->getProductsFilter());
        }
    }
}
