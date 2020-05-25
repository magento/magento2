<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use Magento\Backend\Model\Session;
use Magento\CatalogInventory\Helper\Data;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\Manager as ManagerMessage;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item as ItemCreditmemo;
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
     * @var OrderFactory|MockObject
     */
    private $orderFactoryMock;

    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    private $invoiceRepositoryMock;

    /**
     * @var Manager|MockObject
     */
    private $eventManagerMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var ManagerMessage|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var Data|MockObject
     */
    private $helperMock;

    /**
     * @var Configuration|MockObject
     */
    private $stockConfigurationMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->creditmemoRepositoryMock = $this->createMock(CreditmemoRepositoryInterface::class);
        $this->creditmemoFactoryMock = $this->createMock(CreditmemoFactory::class);
        $this->orderFactoryMock = $this->createMock(OrderFactory::class);
        $this->invoiceRepositoryMock = $this->createMock(InvoiceRepositoryInterface::class);
        $this->eventManagerMock = $this->createMock(Manager::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->messageManagerMock = $this->createMock(ManagerMessage::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->stockConfigurationMock = $this->createMock(Configuration::class);
        $data = [];

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->loader = $this->objectManagerHelper->getObject(
            CreditmemoLoader::class,
            [
               'creditmemoRepository' => $this->creditmemoRepositoryMock,
               'creditmemoFactory' => $this->creditmemoFactoryMock,
               'orderFactory' => $this->orderFactoryMock,
               'invoiceRepository' => $this->invoiceRepositoryMock,
               'eventManager' => $this->eventManagerMock,
               'session' => $this->sessionMock,
               'messageManager' => $this->messageManagerMock,
               'registry' => $this->registryMock,
               'stockConfiguration' => $this->stockConfigurationMock,
               'helper' => $data
            ]
        );
    }

    public function testLoadByCreditmemoId(): void
    {
        $this->loader->setCreditmemoId(1);
        $this->loader->setOrderId(1);
        $this->loader->setCreditmemo('test');
        $creditmemoMock = $this->createMock(Creditmemo::class);
        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($creditmemoMock);

        $this->assertInstanceOf(Creditmemo::class, $this->loader->load());
    }

    public function testLoadCannotCreditmemo(): void
    {
        $orderId = 1234;
        $invoiceId = 99;
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);
        $this->loader->setCreditmemo('test');
        $this->loader->setInvoiceId($invoiceId);

        $orderMock = $this->createMock(Order::class);
        $invoiceMock = $this->createMock(Invoice::class);

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

    public function testLoadByOrder(): void
    {
        $orderId = 1234;
        $invoiceId = 99;
        $qty = 1;
        $data = ['items' => [1 => ['qty' => $qty, 'back_to_stock' => true]]];
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);
        $this->loader->setCreditmemo($data);
        $this->loader->setInvoiceId($invoiceId);

        $orderMock = $this->createMock(Order::class);
        $invoiceMock = $this->createMock(Invoice::class);
        $creditmemoMock = $this->createMock(Creditmemo::class);
        $orderItemMock = $this->createMock(Item::class);
        $creditmemoItemMock = $this->createMock(ItemCreditmemo::class);

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
        $invoiceMock->expects($this->any())
            ->method('setOrder')
            ->willReturnSelf();
        $invoiceMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

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
}
