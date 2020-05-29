<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddressModel;
use Magento\Quote\Model\ResourceModel\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteAddressTest extends TestCase
{
    /**
     * @var Address
     */
    protected $addressResource;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $appResourceMock;

    /**
     * @var QuoteAddressModel|MockObject
     */
    protected $addressMock;

    /**
     * @var Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var Snapshot|MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var RelationComposite|MockObject
     */
    protected $relationCompositeMock;

    /**
     * Init
     */
    protected function setUp(): void
    {
        $this->addressMock = $this->getMockBuilder(QuoteAddressModel::class)
            ->addMethods(['getOrderId', 'getOrder'])
            ->onlyMethods(['__wakeup', 'hasDataChanges', 'beforeSave', 'afterSave', 'validateBeforeSave'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->createPartialMock(Quote::class, ['__wakeup', 'getId']);
        $this->appResourceMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->entitySnapshotMock = $this->createMock(
            Snapshot::class
        );
        $this->relationCompositeMock = $this->createMock(
            RelationComposite::class
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $objectManager = new ObjectManager($this);
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->willReturn([]);
        $this->connectionMock->expects($this->any())
            ->method('insert');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');
        $this->addressResource = $objectManager->getObject(
            Address::class,
            [
                'resource' => $this->appResourceMock,
                'entitySnapshot' => $this->entitySnapshotMock,
                'entityRelationComposite' => $this->relationCompositeMock
            ]
        );
    }

    public function testSave()
    {
        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->addressMock)
            ->willReturn(true);
        $this->entitySnapshotMock->expects($this->once())
            ->method('registerSnapshot')
            ->with($this->addressMock);
        $this->relationCompositeMock->expects($this->once())
            ->method('processRelations')
            ->with($this->addressMock);
        $this->addressResource->save($this->addressMock);
    }
}
