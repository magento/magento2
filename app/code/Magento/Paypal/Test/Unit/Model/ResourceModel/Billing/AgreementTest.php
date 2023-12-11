<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\ResourceModel\Billing;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Paypal\Model\ResourceModel\Billing\Agreement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AgreementTest extends TestCase
{
    /**
     * @var Agreement
     */
    protected $agreementResource;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $collectionMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceConnectionMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->createPartialMock(ResourceConnection::class, [
            'getConnection',
            'getTableName'
        ]);
        $this->collectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSelect'])
            ->getMockForAbstractClass();
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->selectMock = $this->createMock(Select::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceConnectionMock);
        $this->agreementResource = $objectManager->getObject(
            Agreement::class,
            [
                'context' => $contextMock,
            ]
        );
    }

    public function testAddOrdersFilter()
    {
        $this->resourceConnectionMock->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with('paypal_billing_agreement_order')
            ->willReturn('pref_paypal_billing_agreement_order');
        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())
            ->method('from')
            ->with(['pbao' => 'pref_paypal_billing_agreement_order'], ['order_id'], null)
            ->willReturnSelf();
        $this->selectMock->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(
                ['pbao.agreement_id IN(?)', [100]],
                ['main_table.entity_id IN (?)', [500]]
            )
            ->willReturnSelf();
        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->selectMock, [])
            ->willReturn([500]);
        $this->collectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($this->selectMock);
        $this->assertEquals(
            $this->agreementResource,
            $this->agreementResource->addOrdersFilter($this->collectionMock, 100)
        );
    }
}
