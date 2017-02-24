<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;

/**
 * Class QuoteAddressTest
 */
class QuoteAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address
     */
    protected $addressResource;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var RelationComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationCompositeMock;

    /**
     * Init
     */
    protected function setUp()
    {
        $this->addressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['__wakeup', 'getOrderId', 'hasDataChanges', 'beforeSave', 'afterSave', 'validateBeforeSave', 'getOrder'],
            [],
            '',
            false
        );
        $this->quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['__wakeup', 'getId'],
            [],
            '',
            false
        );
        $this->appResourceMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );
        $this->connectionMock = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            [],
            [],
            '',
            false
        );
        $this->entitySnapshotMock = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class,
            [],
            [],
            '',
            false
        );
        $this->relationCompositeMock = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite::class,
            [],
            [],
            '',
            false
        );
        $this->appResourceMock->expects($this->any())
                              ->method('getConnection')
                              ->will($this->returnValue($this->connectionMock));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->connectionMock->expects($this->any())
                          ->method('describeTable')
                          ->will($this->returnValue([]));
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
