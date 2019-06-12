<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Action;

<<<<<<< HEAD
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator;
<<<<<<< HEAD
=======
use PHPUnit\Framework\MockObject\MockObject as MockObject;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FullTest extends \PHPUnit\Framework\TestCase
{
    /**
<<<<<<< HEAD
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var Full|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $model;

    /**
<<<<<<< HEAD
     * @var DecimalFactory|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var DecimalFactory|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $eavDecimalFactory;

    /**
<<<<<<< HEAD
     * @var SourceFactory|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var SourceFactory|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $eavSourceFactory;

    /**
<<<<<<< HEAD
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var MetadataPool|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $metadataPool;

    /**
<<<<<<< HEAD
     * @var BatchProviderInterface|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var BatchProviderInterface|MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $batchProvider;

    /**
<<<<<<< HEAD
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
=======
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
    protected function setUp()
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            [
                'eavDecimalFactory' => $this->eavDecimalFactory,
                'eavSourceFactory' => $this->eavSourceFactory,
                'metadataPool' => $this->metadataPool,
                'batchProvider' => $this->batchProvider,
                'batchSizeCalculator' => $this->batchSizeCalculator,
                'activeTableSwitcher' => $this->activeTableSwitcher,
<<<<<<< HEAD
                'scopeConfig' => $this->scopeConfig
=======
                'scopeConfig' => $this->scopeConfig,
                'batchQueryGenerator' => $this->batchQueryGenerator,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

        $this->eavDecimalFactory->expects($this->once())->method('create')->will($this->returnValue($eavSource));

        $this->eavSourceFactory->expects($this->once())->method('create')->will($this->returnValue($eavDecimal));

<<<<<<< HEAD
        $entityMetadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
=======
        $entityMetadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->getMockForAbstractClass();

        $this->metadataPool->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadataMock);

<<<<<<< HEAD
        $this->batchProvider->expects($this->atLeastOnce())
            ->method('getBatches')
            ->willReturn([['from' => 10, 'to' => 100]]);
        $this->batchProvider->expects($this->atLeastOnce())
            ->method('getBatchIds')
            ->willReturn($ids);

        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->method('select')->willReturn($selectMock);
        $selectMock->expects($this->atLeastOnce())->method('distinct')->willReturnSelf();
        $selectMock->expects($this->atLeastOnce())->method('from')->willReturnSelf();

        $this->model->execute();
    }

<<<<<<< HEAD
=======
    /**
     * @return void
     * @throws LocalizedException
     */
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    public function testExecuteWithDisabledEavIndexer()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn(0);
        $this->metadataPool->expects($this->never())->method('getMetadata');
        $this->model->execute();
    }
}
