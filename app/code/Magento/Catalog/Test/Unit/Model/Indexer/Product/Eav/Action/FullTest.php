<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Action;

use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FullTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var DecimalFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavDecimalFactory;

    /**
     * @var SourceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavSourceFactory;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var BatchProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $batchProvider;

    /**
     * @var BatchSizeCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $batchSizeCalculator;

    /**
     * @var ActiveTableSwitcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $activeTableSwitcher;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->eavDecimalFactory = $this->createPartialMock(DecimalFactory::class, ['create']);
        $this->eavSourceFactory = $this->createPartialMock(SourceFactory::class, ['create']);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->batchProvider = $this->getMockForAbstractClass(BatchProviderInterface::class);
        $this->batchSizeCalculator = $this->createMock(BatchSizeCalculator::class);
        $this->activeTableSwitcher = $this->createMock(ActiveTableSwitcher::class);
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full::class,
            [
                'eavDecimalFactory' => $this->eavDecimalFactory,
                'eavSourceFactory' => $this->eavSourceFactory,
                'metadataPool' => $this->metadataPool,
                'batchProvider' => $this->batchProvider,
                'batchSizeCalculator' => $this->batchSizeCalculator,
                'activeTableSwitcher' => $this->activeTableSwitcher,
                'scopeConfig' => $this->scopeConfig
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
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMockForAbstractClass();

        $connectionMock->expects($this->atLeastOnce())->method('describeTable')->willReturn(['id' => []]);
        $eavSource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eavDecimal = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\Decimal::class)
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

        $this->eavDecimalFactory->expects($this->once())->method('create')->will($this->returnValue($eavSource));

        $this->eavSourceFactory->expects($this->once())->method('create')->will($this->returnValue($eavDecimal));

        $entityMetadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->getMockForAbstractClass();

        $this->metadataPool->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entityMetadataMock);

        $this->batchProvider->expects($this->atLeastOnce())
            ->method('getBatches')
            ->willReturn([['from' => 10, 'to' => 100]]);
        $this->batchProvider->expects($this->atLeastOnce())
            ->method('getBatchIds')
            ->willReturn($ids);

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->method('select')->willReturn($selectMock);
        $selectMock->expects($this->atLeastOnce())->method('distinct')->willReturnSelf();
        $selectMock->expects($this->atLeastOnce())->method('from')->willReturnSelf();

        $this->model->execute();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteWithDisabledEavIndexer()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn(0);
        $this->metadataPool->expects($this->never())->method('getMetadata');
        $this->model->execute();
    }
}
