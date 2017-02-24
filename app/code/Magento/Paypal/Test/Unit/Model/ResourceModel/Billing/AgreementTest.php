<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Model\ResourceModel\Billing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class AgreementTest
 */
class AgreementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement
     */
    protected $agreementResource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceConnectionMock;

    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);
        $contextMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [
                'getConnection',
                'getTableName'
            ],
            [],
            '',
            false
        );
        $this->collectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSelect'])
            ->getMockForAbstractClass();
        $this->connectionMock = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            [],
            [],
            '',
            false
        );
        $this->selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceConnectionMock);
        $this->agreementResource = $objectManager->getObject(
            \Magento\Paypal\Model\ResourceModel\Billing\Agreement::class,
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
