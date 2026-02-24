<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Attribute;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as CreditmemoResource;
use Magento\SalesSequence\Model\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoTest extends TestCase
{
    /**
     * @var CreditmemoResource
     */
    private CreditmemoResource $resource;

    /**
     * @var Creditmemo|MockObject
     */
    private Creditmemo|MockObject $creditmemo;

    /**
     * @var Order|MockObject
     */
    private Order|MockObject $order;

    /**
     * @var Address|MockObject
     */
    private Address|MockObject $billingAddress;

    /**
     * @var Invoice|MockObject
     */
    private Invoice|MockObject $invoice;

    protected function setUp(): void
    {
        $this->creditmemo = $this->createPartialMock(
            Creditmemo::class,
            [
                'getOrderId', 'setOrderId', 'getOrder',
                'getInvoiceId', 'setInvoiceId', 'getInvoice',
                'setBillingAddressId', 'beforeSave', 'afterSave',
                'validateBeforeSave', 'hasDataChanges', 'getStore',
                'getEntityId', 'getIncrementId', 'setIncrementId', 'getEntityType'
            ]
        );
        $this->order = $this->createPartialMock(Order::class, ['getId', 'getBillingAddress', 'getStore']);
        $this->billingAddress = $this->createPartialMock(Address::class, ['getId']);
        $this->invoice = $this->createPartialMock(Invoice::class, ['getId']);

        $resourceConnection = $this->createMock(ResourceConnection::class);
        $connection = $this->createMock(Mysql::class);
        $snapshot = $this->createMock(Snapshot::class);
        $relationComposite = $this->createMock(RelationComposite::class);
        $attribute = $this->createMock(Attribute::class);
        $sequenceManager = $this->createMock(Manager::class);
        $sequence = $this->createMock(\Magento\SalesSequence\Model\Sequence::class);
        $transactionManager = $this->createMock(TransactionManagerInterface::class);
        $objectRelationProcessor = $this->createMock(ObjectRelationProcessor::class);

        $resourceConnection->method('getConnection')->willReturn($connection);
        $connection->method('describeTable')->willReturn([]);
        $connection->method('insert');
        $connection->method('lastInsertId');

        $context = new Context($resourceConnection, $transactionManager, $objectRelationProcessor);

        // Sequence mock so parent::_beforeSave can set increment id
        $sequenceManager->method('getSequence')
            ->with('creditmemo', 1)
            ->willReturn($sequence);
        $sequence->method('getNextValue')->willReturn('100000001');
        $this->creditmemo->method('getEntityType')->willReturn('creditmemo');
        $this->creditmemo->method('getEntityId')->willReturn(null);
        $this->creditmemo->method('getIncrementId')->willReturn(null);
        $this->creditmemo->method('setIncrementId')->willReturnSelf();

        // Store mock to satisfy parent logic
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->method('getId')->willReturn(1);
        $this->creditmemo->method('getStore')->willReturn($store);
        $this->order->method('getStore')->willReturn($store);

        $this->resource = new CreditmemoResource(
            $context,
            $snapshot,
            $relationComposite,
            $attribute,
            $sequenceManager
        );
    }

    public function testSetsOrderAndBillingAddressWhenMissing(): void
    {
        $this->creditmemo->method('getOrderId')->willReturn(null);
        $this->creditmemo
            ->expects($this->atLeastOnce())
            ->method('getOrder')
            ->willReturn($this->order);
        $this->creditmemo
            ->expects($this->once())
            ->method('setOrderId')
            ->with(10)->willReturnSelf();
        $this->creditmemo
            ->expects($this->once())
            ->method('setBillingAddressId')
            ->with(20)
            ->willReturnSelf();

        $this->order->method('getId')->willReturn(10);
        $this->order
            ->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($this->billingAddress);
        $this->billingAddress
            ->expects($this->once())
            ->method('getId')
            ->willReturn(20);

        $this->creditmemo->method('getInvoiceId')->willReturn(null);
        $this->creditmemo->method('getInvoice')->willReturn(null);

        $this->invokeBeforeSave();
    }

    public function testSkipsOrderFieldsWhenOrderIdExists(): void
    {
        $this->creditmemo->method('getOrderId')->willReturn(10);
        $this->creditmemo
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->order);
        $this->creditmemo->expects($this->never())->method('setOrderId');
        $this->creditmemo->expects($this->never())->method('setBillingAddressId');

        $this->creditmemo->method('getInvoiceId')->willReturn(null);
        $this->creditmemo->method('getInvoice')->willReturn(null);

        $this->invokeBeforeSave();
    }

    public function testSetsInvoiceIdWhenMissing(): void
    {
        $this->creditmemo->method('getOrderId')->willReturn(10);
        $this->creditmemo
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->order);

        $this->creditmemo->method('getInvoiceId')->willReturn(null);
        $this->creditmemo
            ->expects($this->once())
            ->method('getInvoice')
            ->willReturn($this->invoice);
        $this->creditmemo
            ->expects($this->once())
            ->method('setInvoiceId')
            ->with(30)
            ->willReturnSelf();
        $this->invoice->expects($this->once())->method('getId')->willReturn(30);

        $this->invokeBeforeSave();
    }

    private function invokeBeforeSave(): void
    {
        $method = new \ReflectionMethod(CreditmemoResource::class, '_beforeSave');
        $method->setAccessible(true);
        $method->invoke($this->resource, $this->creditmemo);
    }
}
