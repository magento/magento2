<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Eav\Action\Full;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FullTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Full|MockObject
     */
    private $model;

    /**
     * @var DecimalFactory|MockObject
     */
    private $eavDecimalFactory;

    /**
     * @var SourceFactory|MockObject
     */
    private $eavSourceFactory;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var BatchProviderInterface|MockObject
     */
    private $batchProvider;

    /**
     * @var BatchSizeCalculator|MockObject
     */
    private $batchSizeCalculator;

    /**
     * @var ActiveTableSwitcher|MockObject
     */
    private $activeTableSwitcher;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var Generator
     */
    private $batchQueryGenerator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->eavDecimalFactory = $this->createPartialMock(DecimalFactory::class, ['create']);
        $this->eavSourceFactory = $this->createPartialMock(SourceFactory::class, ['create']);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->batchProvider = $this->getMockForAbstractClass(BatchProviderInterface::class);
        $this->batchQueryGenerator = $this->createMock(Generator::class);
        $this->batchSizeCalculator = $this->createMock(BatchSizeCalculator::class);
        $this->activeTableSwitcher = $this->createMock(ActiveTableSwitcher::class);
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Full::class,
            [
                'eavDecimalFactory' => $this->eavDecimalFactory,
                'eavSourceFactory' => $this->eavSourceFactory,
                'metadataPool' => $this->metadataPool,
                'batchProvider' => $this->batchProvider,
                'batchSizeCalculator' => $this->batchSizeCalculator,
                'activeTableSwitcher' => $this->activeTableSwitcher,
                'scopeConfig' => $this->scopeConfig,
                'batchQueryGenerator' => $this->batchQueryGenerator,
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn(1);

        $ids = [1, 2, 3];
        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();

        $connectionMock->expects($this->atLeastOnce())->method('describeTable')->willReturn(['id' => []]);
        $eavSource = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder(Decimal::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavSource->expects($this->once())->method('getRelationsByChild')->with($ids)->willReturn([]);
        $eavSource->expects($this->never())->method('getRelationsByParent')->with($ids)->willReturn([]);

        $eavDecimal->expects($this->once())->method('getRelationsByChild')->with($ids)->willReturn([]);
        $eavDecimal->expects($this->never())->method('getRelationsByParent')->with($ids)->willReturn([]);

        $eavSource->expects($this->atLeastOnce())->method('getConnection')->willReturn($connectionMock);
        $eavDecimal->expects($this->atLeastOnce())->method('getConnection')->willReturn($connectionMock);

        $eavDecimal->expects($this->once())->method('reindexEntities')->with($ids);

        $eavSource->expects($this->once())->method('reindexEntities')->with($ids);

        $this->eavDecimalFactory->expects($this->once())->method('create')->willReturn($eavSource);

        $this->eavSourceFactory->expects($this->once())->method('create')->willReturn($eavDecimal);

        $entityMetadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->metadataPool->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadataMock);

        // Super inefficient algorithm in some cases
        $this->batchProvider->expects($this->never())
            ->method('getBatches');

        $batchQuery = $this->createMock(Select::class);

        $connectionMock->method('fetchCol')
            ->with($batchQuery)
            ->willReturn($ids);

        $this->batchQueryGenerator->method('generate')
            ->willReturn([$batchQuery]);

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->method('select')->willReturn($selectMock);
        $selectMock->expects($this->atLeastOnce())->method('distinct')->willReturnSelf();
        $selectMock->expects($this->atLeastOnce())->method('from')->willReturnSelf();

        $this->model->execute();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testExecuteWithDisabledEavIndexer()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn(0);
        $this->metadataPool->expects($this->never())->method('getMetadata');
        $this->model->execute();
    }
}
