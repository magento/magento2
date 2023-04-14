<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\Action;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Factory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\TierPrice;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\Indexer\Product\Price\Action\Row;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\MultiDimensionProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowDefaultPriceIndexerTest extends TestCase
{
    /**
     * @var Row
     */
    private $actionRow;

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

        $this->actionRow = new Row(
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
            $this->tableMaintainer
        );
    }

    /**
     * Test that the price indexer will be able to perform the indexation with DefaultPrice indexer
     *
     * @return void
     * @throws InputException
     * @throws LocalizedException
     */
    public function testRowDefaultPriceIndexer()
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('joinLeft')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->method('join')->willReturnSelf();

        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('select')->willReturn($select);
        $adapter->method('describeTable')->willReturn([]);

        $adapter->expects($this->exactly(1))
            ->method('describeTable');

        $this->tableMaintainer->expects($this->exactly(3))->method('getMainTableByDimensions');

        $this->defaultIndexerResource->method('getConnection')->willReturn($adapter);
        $adapter->method('fetchAll')->with($select)->willReturn([]);

        $adapter->expects($this->any())
            ->method('fetchPairs')
            ->with($select)
            ->willReturn(
                [1 => 'simple'],
                []
            );

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
                    'simple' => ['price_indexer' => '\Price\Indexer']
                ]
            );
        $this->indexerPriceFactory->expects($this->exactly(1))
            ->method('create')
            ->with('\Price\Indexer', ['fullReindexAction' => false])
            ->willReturn($this->defaultIndexerResource);
        $this->defaultIndexerResource->expects($this->exactly(1))
            ->method('reindexEntity');
        $this->defaultIndexerResource->expects($this->any())->method('setTypeId')->willReturnSelf();
        $this->defaultIndexerResource->expects($this->any())->method('setIsComposite');
        $select->expects($this->exactly(1))
            ->method('deleteFromSelect')
            ->with('index_price')
            ->willReturn('');
        $adapter->expects($this->exactly(2))
            ->method('getIndexList')
            ->willReturn(['entity_id'=>['COLUMNS_LIST'=>['test']]]);
        $adapter->expects($this->exactly(2))
            ->method('getPrimaryKeyName')
            ->willReturn('entity_id');

        $this->actionRow->execute(1);
    }
}
