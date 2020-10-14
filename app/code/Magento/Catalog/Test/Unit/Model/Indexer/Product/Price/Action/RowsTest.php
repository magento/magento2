<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\Action;

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
        $this->config = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDate = $this->getMockBuilder(TimezoneInterface::class)
            ->getMockForAbstractClass();
        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogProductType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexerPriceFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->defaultIndexerResource = $this->getMockBuilder(DefaultPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tierPriceIndexResource = $this->getMockBuilder(TierPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dimensionCollectionFactory = $this->getMockBuilder(DimensionCollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tableMaintainer = $this->getMockBuilder(TableMaintainer::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Bad value was supplied.');
        $this->actionRows->execute(null);
    }

    public function testBatchProcessing()
    {
        $ids = [1, 2, 3, 4];
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();
        $select->expects($this->any())->method('join')->willReturnSelf();
        $adapter = $this->getMockBuilder(AdapterInterface::class)->getMockForAbstractClass();
        $adapter->expects($this->any())->method('select')->willReturn($select);
        $this->defaultIndexerResource->expects($this->any())
            ->method('getConnection')
            ->willReturn($adapter);
        $adapter->expects($this->any())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([]);
        $adapter->expects($this->any())
            ->method('fetchPairs')
            ->with($select)
            ->willReturn([]);
        $multiDimensionProvider = $this->getMockBuilder(MultiDimensionProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dimensionCollectionFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($multiDimensionProvider);
        $iterator = new \ArrayIterator([]);
        $multiDimensionProvider->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn($iterator);
        $this->catalogProductType->expects($this->any())
            ->method('getTypesByPriority')
            ->willReturn([]);
        $adapter->expects($this->exactly(2))
            ->method('getIndexList')
            ->willReturn(['entity_id'=>['COLUMNS_LIST'=>['test']]]);
        $adapter->expects($this->exactly(2))
            ->method('getPrimaryKeyName')
            ->willReturn('entity_id');
        $this->actionRows->execute($ids);
    }
}
