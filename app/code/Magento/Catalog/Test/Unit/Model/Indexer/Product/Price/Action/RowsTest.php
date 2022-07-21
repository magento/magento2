<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\Action;

use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\Indexer\Product\Price\Action\Rows;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\MultiDimensionProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test coverage for the rows action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) to preserve compatibility with parent class
 */
class RowsTest extends TestCase
{
    /**
     * @var Rows
     */
    private $actionRows;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $config;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var CurrencyFactory|MockObject
     */
    private $currencyFactory;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDate;

    /**
     * @var DateTime|MockObject
     */
    private $dateTime;

    /**
     * @var Type|MockObject
     */
    private $catalogProductType;

    /**
     * @var Factory|MockObject
     */
    private $indexerPriceFactory;

    /**
     * @var DefaultPrice|MockObject
     */
    private $defaultIndexerResource;

    /**
     * @var TierPrice|MockObject
     */
    private $tierPriceIndexResource;

    /**
     * @var DimensionCollectionFactory|MockObject
     */
    private $dimensionCollectionFactory;

    /**
     * @var TableMaintainer|MockObject
     */
    private $tableMaintainer;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ScopeConfigInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->currencyFactory = $this->createMock(CurrencyFactory::class);
        $this->localeDate = $this->createMock(TimezoneInterface::class);
        $this->dateTime = $this->createMock(DateTime::class);
        $this->catalogProductType = $this->createMock(Type::class);
        $this->indexerPriceFactory = $this->createMock(Factory::class);
        $this->defaultIndexerResource = $this->createMock(DefaultPrice::class);
        $this->tierPriceIndexResource = $this->createMock(TierPrice::class);
        $this->dimensionCollectionFactory = $this->createMock(DimensionCollectionFactory::class);
        $this->tableMaintainer = $this->createMock(TableMaintainer::class);
        $batchSize = 2;

        $this->actionRows = new Rows(
            $this->config,
            $this->storeManager,
            $this->currencyFactory,
            $this->localeDate,
            $this->dateTime,
            $this->catalogProductType,
            $this->indexerPriceFactory,
            $this->defaultIndexerResource,
            $this->tierPriceIndexResource,
            $this->dimensionCollectionFactory,
            $this->tableMaintainer,
            $batchSize
        );
    }

    public function testEmptyIds()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('Bad value was supplied.');
        $this->actionRows->execute(null);
    }

    public function testBatchProcessing()
    {
        $ids = [1, 2, 3, 4];

        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('joinLeft')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('join')->willReturnSelf();
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('select')->willReturn($select);
        $adapter->method('describeTable')->willReturn([]);
        $this->defaultIndexerResource->method('getConnection')->willReturn($adapter);
        $adapter->method('fetchAll')->with($select)->willReturn([]);

        $adapter->expects($this->exactly(4))
            ->method('fetchPairs')
            ->with($select)
            ->willReturnOnConsecutiveCalls(
                [1 => 'simple', 2 => 'virtual'],
                [],
                [3 => 'simple', 4 => 'virtual'],
                [],
            );
        $multiDimensionProvider = $this->createMock(MultiDimensionProvider::class);
        $this->dimensionCollectionFactory->expects($this->exactly(4))
            ->method('create')
            ->willReturn($multiDimensionProvider);
        $dimension = $this->createMock(Dimension::class);
        $dimension->method('getName')->willReturn('default');
        $dimension->method('getValue')->willReturn('0');
        $iterator = new \ArrayIterator([[$dimension]]);
        $multiDimensionProvider->expects($this->exactly(4))
            ->method('getIterator')
            ->willReturn($iterator);
        $this->catalogProductType->expects($this->once())
            ->method('getTypesByPriority')
            ->willReturn(
                [
                    'virtual' => ['price_indexer' => '\Price\Indexer'],
                    'simple' => ['price_indexer' => '\Price\Indexer'],
                ]
            );
        $priceIndexer = $this->createMock(DimensionalIndexerInterface::class);
        $this->indexerPriceFactory->expects($this->exactly(2))
            ->method('create')
            ->with('\Price\Indexer', ['fullReindexAction' => false])
            ->willReturn($priceIndexer);
        $priceIndexer->expects($this->exactly(4))
            ->method('executeByDimensions');
        $select->expects($this->exactly(4))
            ->method('deleteFromSelect')
            ->with('main_table')
            ->willReturn('');
        $adapter->expects($this->exactly(2))
            ->method('getIndexList')
            ->willReturn(['entity_id'=>['COLUMNS_LIST'=>['test']]]);
        $adapter->expects($this->exactly(2))
            ->method('getPrimaryKeyName')
            ->willReturn('entity_id');

        $this->actionRows->execute($ids);
    }

    public function testDeletedProductsBatchProcessing()
    {
        $ids = [1, 2, 3, 4];

        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('joinLeft')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('join')->willReturnSelf();
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('select')->willReturn($select);
        $adapter->method('describeTable')->willReturn([]);
        $this->defaultIndexerResource->method('getConnection')->willReturn($adapter);
        $adapter->method('fetchAll')->with($select)->willReturn([]);

        $adapter->expects($this->exactly(4))
            ->method('fetchPairs')
            ->with($select)
            ->willReturnOnConsecutiveCalls([], [], [], []);
        $multiDimensionProvider = $this->createMock(MultiDimensionProvider::class);
        $this->dimensionCollectionFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($multiDimensionProvider);
        $dimension = $this->createMock(Dimension::class);
        $dimension->method('getName')->willReturn('default');
        $dimension->method('getValue')->willReturn('0');
        $iterator = new \ArrayIterator([[$dimension]]);
        $multiDimensionProvider->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn($iterator);
        $this->catalogProductType->expects($this->once())
            ->method('getTypesByPriority')
            ->willReturn(
                [
                    'virtual' => ['price_indexer' => '\Price\Indexer'],
                    'simple' => ['price_indexer' => '\Price\Indexer'],
                ]
            );
        $priceIndexer = $this->createMock(DimensionalIndexerInterface::class);
        $this->indexerPriceFactory->expects($this->exactly(2))
            ->method('create')
            ->with('\Price\Indexer', ['fullReindexAction' => false])
            ->willReturn($priceIndexer);
        $priceIndexer->expects($this->never())
            ->method('executeByDimensions');
        $select->expects($this->exactly(2))
            ->method('deleteFromSelect')
            ->with('index_price')
            ->willReturn('');
        $adapter->expects($this->exactly(2))
            ->method('getIndexList')
            ->willReturn(['entity_id'=>['COLUMNS_LIST'=>['test']]]);
        $adapter->expects($this->exactly(2))
            ->method('getPrimaryKeyName')
            ->willReturn('entity_id');

        $this->actionRows->execute($ids);
    }
}
