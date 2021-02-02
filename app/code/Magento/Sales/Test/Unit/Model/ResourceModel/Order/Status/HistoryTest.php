<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Status;

/**
 * Class HistoryTest
 * @package Magento\Sales\Model\ResourceModel\Order\Status
 */
class HistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\History
     */
    protected $historyResource;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $historyMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History\Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entitySnapshotMock;

    protected function setUp(): void
    {
        $this->appResourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->validatorMock = $this->createMock(\Magento\Sales\Model\Order\Status\History\Validator::class);
        $this->entitySnapshotMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->willReturn([]);
        $this->connectionMock->expects($this->any())
            ->method('insert');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');

        $relationProcessorMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class
        );

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->appResourceMock);
        $contextMock->expects($this->once())->method('getObjectRelationProcessor')->willReturn($relationProcessorMock);

        $this->historyResource = $objectManager->getObject(
            \Magento\Sales\Model\ResourceModel\Order\Status\History::class,
            [
                'context' => $contextMock,
                'validator' => $this->validatorMock,
                'entitySnapshot' => $this->entitySnapshotMock
            ]
        );
    }

    /**
     * test _beforeSaveMethod via save()
     */
    public function testSave()
    {
        $historyMock = $this->createMock(\Magento\Sales\Model\Order\Status\History::class);
        $this->entitySnapshotMock->expects($this->once())->method('isModified')->with($historyMock)->willReturn(true);
        $historyMock->expects($this->any())->method('isSaveAllowed')->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($historyMock)
            ->willReturn([]);
        $historyMock->expects($this->any())->method('getData')->willReturn([]);
        $this->historyResource->save($historyMock);
    }

    /**
     * test _beforeSaveMethod via save()
     */
    public function testValidate()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Cannot save comment:');

        $historyMock = $this->createMock(\Magento\Sales\Model\Order\Status\History::class);
        $this->entitySnapshotMock->expects($this->once())->method('isModified')->with($historyMock)->willReturn(true);
        $historyMock->expects($this->any())->method('isSaveAllowed')->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($historyMock)
            ->willReturn(['Some warnings']);
        $this->assertEquals($this->historyResource, $this->historyResource->save($historyMock));
    }
}
