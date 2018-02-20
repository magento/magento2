<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sequence\SequenceInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\SalesSequence\Model\Manager;

class QuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceConnection
     */
    private $resourceMock;

    /**
     * @var Mysql
     */
    private $adapterMock;

    /**
     * @var Select
     */
    private $selectMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sequenceManagerMock;

    /**
     * @var SequenceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sequenceMock;

    /**
     * @var QuoteResource
     */
    private $model;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $this->selectMock->expects($this->any())->method('where');

        $this->adapterMock = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapterMock->expects($this->any())->method('select')->will($this->returnValue($this->selectMock));

        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->adapterMock)
        );

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects(
            $this->once()
        )->method(
            'getResources'
        )->will(
            $this->returnValue($this->resourceMock)
        );

        $snapshot = $this->getMockBuilder(Snapshot::class)
            ->disableOriginalConstructor()
            ->getMock();
        $relationComposite = $this->getMockBuilder(RelationComposite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceMock = $this->getMockBuilder(SequenceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManagerHelper->getObject(
            QuoteResource::class,
            [
                'context' => $context,
                'entitySnapshot' => $snapshot,
                'entityRelationComposite' => $relationComposite,
                'sequenceManager' => $this->sequenceManagerMock,
                'connectionName' => null,
            ]
        );
    }

    /**
     * Unit test to verify if isOrderIncrementIdUsed method works with different types increment ids
     *
     * @param array $value
     * @dataProvider isOrderIncrementIdUsedDataProvider
     */
    public function testIsOrderIncrementIdUsed($value)
    {
        $expectedBind = [':increment_id' => $value];
        $this->adapterMock->expects($this->once())->method('fetchOne')->with($this->selectMock, $expectedBind);
        $this->model->isOrderIncrementIdUsed($value);
    }

    /**
     * @return array
     */
    public function isOrderIncrementIdUsedDataProvider()
    {
        return [[100000001], ['10000000001'], ['M10000000001']];
    }

    /**
     * /**
     * @param $entityType
     * @param $storeId
     * @param $reservedOrderId
     * @dataProvider getReservedOrderIdDataProvider
     */
    public function testGetReservedOrderId($entityType, $storeId, $reservedOrderId)
    {
        $this->sequenceManagerMock->expects($this->once())
            ->method('getSequence')
            ->with($entityType, $storeId)
            ->willReturn($this->sequenceMock);
        $this->quoteMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->sequenceMock->expects($this->once())
            ->method('getNextValue')
            ->willReturn($reservedOrderId);

        $this->assertEquals($reservedOrderId, $this->model->getReservedOrderId($this->quoteMock));
    }

    /**
     * @return array
     */
    public function getReservedOrderIdDataProvider(): array
    {
        return [
            [\Magento\Sales\Model\Order::ENTITY, 1, '1000000001'],
            [\Magento\Sales\Model\Order::ENTITY, 2, '2000000001'],
            [\Magento\Sales\Model\Order::ENTITY, 3, '3000000001']
        ];
    }
}
