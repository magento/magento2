<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;

/**
 * Class QuoteAddressTest
 */
class QuoteAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address
     */
    protected $addressResource;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var RelationComposite|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $relationCompositeMock;

    /**
     * Init
     */
    protected function setUp(): void
    {
        $this->addressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['__wakeup', 'getOrderId', 'hasDataChanges', 'beforeSave', 'afterSave', 'validateBeforeSave', 'getOrder']
        );
        $this->quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['__wakeup', 'getId']);
        $this->appResourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->entitySnapshotMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class
        );
        $this->relationCompositeMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite::class
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
        $this->addressResource = $objectManager->getObject(
            \Magento\Quote\Model\ResourceModel\Quote\Address::class,
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
