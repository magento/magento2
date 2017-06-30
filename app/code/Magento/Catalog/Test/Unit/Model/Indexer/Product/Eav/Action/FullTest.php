<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Action;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FullTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteWithAdapterErrorThrowsException()
    {
        $eavDecimalFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $eavSourceFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $exceptionMessage = 'exception message';
        $exception = new \Exception($exceptionMessage);

        $eavDecimalFactory->expects($this->once())
            ->method('create')
            ->will($this->throwException($exception));

        $metadataMock = $this->getMock(\Magento\Framework\EntityManager\MetadataPool::class, [], [], '', false);
        $batchProviderMock = $this->getMock(\Magento\Framework\Indexer\BatchProviderInterface::class);

        $batchManagementMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator::class,
            [],
            [],
            '',
            false
        );

        $tableSwitcherMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher::class
        )->disableOriginalConstructor()->getMock();

        $model = new \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full(
            $eavDecimalFactory,
            $eavSourceFactory,
            $metadataMock,
            $batchProviderMock,
            $batchManagementMock,
            $tableSwitcherMock
        );

        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class, $exceptionMessage);

        $model->execute();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $eavDecimalFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $eavSourceFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory::class,
            ['create'],
            [],
            '',
            false
        );

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

        $eavDecimal->expects($this->once())
            ->method('reindexEntities')
            ->with($ids);

        $eavSource->expects($this->once())
            ->method('reindexEntities')
            ->with($ids);

        $eavDecimalFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavSource));

        $eavSourceFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($eavDecimal));

        $metadataMock = $this->getMock(\Magento\Framework\EntityManager\MetadataPool::class, [], [], '', false);
        $entityMetadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->getMockForAbstractClass();

        $metadataMock->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entityMetadataMock);

        $batchProviderMock = $this->getMock(\Magento\Framework\Indexer\BatchProviderInterface::class);
        $batchProviderMock->expects($this->atLeastOnce())
            ->method('getBatches')
            ->willReturn([['from' => 10, 'to' => 100]]);
        $batchProviderMock->expects($this->atLeastOnce())
            ->method('getBatchIds')
            ->willReturn($ids);

        $batchManagementMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\BatchSizeCalculator::class,
            [],
            [],
            '',
            false
        );
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->method('select')->willReturn($selectMock);
        $selectMock->expects($this->atLeastOnce())->method('distinct')->willReturnSelf();
        $selectMock->expects($this->atLeastOnce())->method('from')->willReturnSelf();

        $tableSwitcherMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher::class
        )->disableOriginalConstructor()->getMock();

        $model = new \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full(
            $eavDecimalFactory,
            $eavSourceFactory,
            $metadataMock,
            $batchProviderMock,
            $batchManagementMock,
            $tableSwitcherMock
        );

        $model->execute();
    }
}
