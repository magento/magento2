<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Shipment;

/**
 * Class TrackTest
 */
class TrackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track
     */
    protected $trackResource;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $trackModelMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track\Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entitySnapshotMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->trackModelMock = $this->createMock(\Magento\Sales\Model\Order\Shipment\Track::class);
        $this->appResourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->validatorMock = $this->createMock(\Magento\Sales\Model\Order\Shipment\Track\Validator::class);
        $this->entitySnapshotMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->willReturn([]);
        $this->connectionMock->expects($this->any())
            ->method('insert');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');
        $this->trackModelMock->expects($this->any())->method('hasDataChanges')->willReturn(true);
        $this->trackModelMock->expects($this->any())->method('isSaveAllowed')->willReturn(true);

        $relationProcessorMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class
        );

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->appResourceMock);
        $contextMock->expects($this->once())->method('getObjectRelationProcessor')->willReturn($relationProcessorMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->trackResource = $objectManager->getObject(
            \Magento\Sales\Model\ResourceModel\Order\Shipment\Track::class,
            [
                'context' => $contextMock,
                'validator' => $this->validatorMock,
                'entitySnapshot' => $this->entitySnapshotMock
            ]
        );
    }

    /**
     * Test _beforeSaveMethod via save()
     */
    public function testSave()
    {
        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->trackModelMock)
            ->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->trackModelMock))
            ->willReturn([]);
        $this->trackModelMock->expects($this->any())->method('getData')->willReturn([]);
        $this->trackResource->save($this->trackModelMock);
        $this->assertTrue(true);
    }

    /**
     * Test _beforeSaveMethod via save() with failed validation
     *
     */
    public function testSaveValidationFailed()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Cannot save track:');

        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->trackModelMock)
            ->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->trackModelMock))
            ->willReturn(['warning message']);
        $this->trackModelMock->expects($this->any())->method('getData')->willReturn([]);
        $this->trackResource->save($this->trackModelMock);
        $this->assertTrue(true);
    }
}
