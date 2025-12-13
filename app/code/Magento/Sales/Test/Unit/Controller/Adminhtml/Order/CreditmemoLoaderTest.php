<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use Magento\Backend\Model\Session;
use Magento\CatalogInventory\Helper\Data;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\Event\Manager;
use Magento\Framework\Registry;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoLoaderTest extends TestCase
{
    /**
     * @var CreditmemoLoader
     */
    private $loader;

    /**
     * @var CreditmemoRepositoryInterface|MockObject
     */
    private $creditmemoRepositoryMock;

    /**
     * @var CreditmemoFactory|MockObject
     */
    private $creditmemoFactoryMock;

    /**
     * @var MockObject
     */
    private $orderFactoryMock;

    /**
     * @var MockObject
     */
    private $invoiceRepositoryMock;

    /**
     * @var MockObject
     */
    private $eventManagerMock;

    /**
     * @var MockObject
     */
    private $sessionMock;

    /**
     * @var MockObject
     */
    private $messageManagerMock;

    /**
     * @var MockObject
     */
    private $registryMock;

    /**
     * @var MockObject
     */
    private $helperMock;

    /**
     * @var MockObject
     */
    private $stockConfiguration;

    protected function setUp(): void
    {
        $data = [];
        $this->creditmemoRepositoryMock = $this->getMockBuilder(CreditmemoRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditmemoFactoryMock = $this->createMock(CreditmemoFactory::class);
        $this->orderFactoryMock = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockConfiguration = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loader = new CreditmemoLoader(
            $this->creditmemoRepositoryMock,
            $this->creditmemoFactoryMock,
            $this->orderFactoryMock,
            $this->invoiceRepositoryMock,
            $this->eventManagerMock,
            $this->sessionMock,
            $this->messageManagerMock,
            $this->registryMock,
            $this->stockConfiguration,
            $data
        );
    }

    public function testLoadByCreditmemoId()
    {
        $this->loader->setCreditmemoId(1);
        $this->loader->setOrderId(1);
        $this->loader->setCreditmemo('test');

        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($creditmemoMock);

        $this->assertInstanceOf(Creditmemo::class, $this->loader->load());
    }

    public function testLoadCannotCreditmemo()
    {
        $orderId = 1234;
        $invoiceId = 99;
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);
        $this->loader->setCreditmemo('test');
        $this->loader->setInvoiceId($invoiceId);

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->willReturn(false);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->any())
            ->method('setOrder')
            ->with($orderMock)
            ->willReturnSelf();
        $invoiceMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->assertFalse($this->loader->load());
    }

    public function testLoadByOrder()
    {
        $orderId = 1234;
        $invoiceId = 99;
        $qty = 1;
        $data = ['items' => [1 => ['qty' => $qty, 'back_to_stock' => true]]];
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);
        $this->loader->setCreditmemo($data);
        $this->loader->setInvoiceId($invoiceId);

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->willReturn(true);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->any())
            ->method('setOrder')
            ->willReturnSelf();
        $invoiceMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);
        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItemMock->expects($this->any())
            ->method('getOrderItem')
            ->willReturn($orderItemMock);
        $items = [$creditmemoItemMock, $creditmemoItemMock, $creditmemoItemMock];
        $creditmemoMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn($items);
        $data['qtys'] = [1 => $qty];
        $this->creditmemoFactoryMock->expects($this->any())
            ->method('createByInvoice')
            ->with($invoiceMock, $data)
            ->willReturn($creditmemoMock);

        $this->assertEquals($creditmemoMock, $this->loader->load());
    }

    public function testLoadByOrderWithoutInvoiceCreatesByOrder()
    {
        $orderId = 9876;
        $qty = 2;
        $data = ['items' => [1 => ['qty' => $qty]]];

        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);
        $this->loader->setCreditmemo($data);
        // intentionally do not set invoice id to force createByOrder path

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->willReturn(true);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);

        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([]); // no items to process back_to_stock in this scenario

        $expectedData = $data;
        $expectedData['qtys'] = [1 => $qty];

        $this->creditmemoFactoryMock->expects($this->once())
            ->method('createByOrder')
            ->with($orderMock, $expectedData)
            ->willReturn($creditmemoMock);

        $this->assertSame($creditmemoMock, $this->loader->load());
    }

    public function testLoadByCreditmemoIdNoLongerExists()
    {
        $this->loader->setCreditmemoId(123);
        $this->loader->setOrderId(1);
        $this->loader->setCreditmemo('test');

        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->willThrowException(new \Exception('not found'));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage');

        $this->assertFalse($this->loader->load());
    }

    public function testLoadOrderNoLongerExists()
    {
        $orderId = 7777;
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $orderMock->expects($this->never())
            ->method('canCreditmemo');
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage');

        $this->assertFalse($this->loader->load());
    }

    public function testLoadAutoReturnBackToStockTrue()
    {
        $orderId = 1111;
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);
        // Do not set creditmemo data to force session form data path

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->willReturn(true);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);

        $creditmemoItem1 = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItem2 = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItem3 = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItem1->expects($this->any())->method('getOrderItem')->willReturn($orderItem);
        $creditmemoItem2->expects($this->any())->method('getOrderItem')->willReturn($orderItem);
        $creditmemoItem3->expects($this->any())->method('getOrderItem')->willReturn($orderItem);

        $this->stockConfiguration->expects($this->any())
            ->method('isAutoReturnEnabled')
            ->willReturn(true);

        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([$creditmemoItem1, $creditmemoItem2, $creditmemoItem3]);

        $this->creditmemoFactoryMock->expects($this->once())
            ->method('createByOrder')
            ->with($orderMock, $this->isType('array'))
            ->willReturn($creditmemoMock);

        $this->assertSame($creditmemoMock, $this->loader->load());
    }

    public function testLoadAutoReturnBackToStockFalse()
    {
        $orderId = 2222;
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);
        // Do not set creditmemo data to force session form data path

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->willReturn(true);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);

        $creditmemoItem1 = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItem2 = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItem3 = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItem1->expects($this->any())->method('getOrderItem')->willReturn($orderItem);
        $creditmemoItem2->expects($this->any())->method('getOrderItem')->willReturn($orderItem);
        $creditmemoItem3->expects($this->any())->method('getOrderItem')->willReturn($orderItem);

        $this->stockConfiguration->expects($this->any())
            ->method('isAutoReturnEnabled')
            ->willReturn(false);

        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([$creditmemoItem1, $creditmemoItem2, $creditmemoItem3]);

        $this->creditmemoFactoryMock->expects($this->once())
            ->method('createByOrder')
            ->with($orderMock, $this->isType('array'))
            ->willReturn($creditmemoMock);

        $this->assertSame($creditmemoMock, $this->loader->load());
    }

    public function testLoadWithoutIdsDispatchesAndRegisters()
    {
        // No creditmemoId and no orderId set
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'adminhtml_sales_order_creditmemo_register_before',
                $this->callback(function ($params) {
                    return is_array($params)
                        && array_key_exists('creditmemo', $params)
                        && array_key_exists('input', $params)
                        && $params['creditmemo'] === false;
                })
            );
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('current_creditmemo', false);

        $this->assertFalse($this->loader->load());
    }

    public function testLoadByOrderWithNegativeQty()
    {
        $orderId = 1234;
        $invoiceId = 99;
        $qty = -1;
        $data = ['items' => [1 => ['qty' => $qty, 'back_to_stock' => true]]];
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);
        $this->loader->setCreditmemo($data);
        $this->loader->setInvoiceId($invoiceId);

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->willReturn(true);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->any())
            ->method('setOrder')
            ->willReturnSelf();
        $invoiceMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);
        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoItemMock->expects($this->any())
            ->method('getOrderItem')
            ->willReturn($orderItemMock);
        $items = [$creditmemoItemMock, $creditmemoItemMock, $creditmemoItemMock];
        $creditmemoMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn($items);
        $data['qtys'] = [1 => 0];
        $this->creditmemoFactoryMock->expects($this->any())
            ->method('createByInvoice')
            ->with($invoiceMock, $data)
            ->willReturn($creditmemoMock);

        $this->assertEquals($creditmemoMock, $this->loader->load());
    }
}
