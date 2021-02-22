<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

/**
 * Class CreditmemoLoaderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    private $loader;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $creditmemoRepositoryMock;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $creditmemoFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $invoiceRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $registryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $stockConfiguration;

    protected function setUp(): void
    {
        $data = [];
        $this->creditmemoRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\CreditmemoRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoFactoryMock = $this->createMock(\Magento\Sales\Model\Order\CreditmemoFactory::class);
        $this->orderFactoryMock = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->invoiceRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\Manager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->helperMock = $this->getMockBuilder(\Magento\CatalogInventory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->stockConfiguration = $this->getMockBuilder(\Magento\CatalogInventory\Model\Configuration::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->loader = new \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader(
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

        $creditmemoMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($creditmemoMock);

        $this->assertInstanceOf(\Magento\Sales\Model\Order\Creditmemo::class, $this->loader->load());
    }

    public function testLoadCannotCreditmemo()
    {
        $orderId = 1234;
        $invoiceId = 99;
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId($orderId);
        $this->loader->setCreditmemo('test');
        $this->loader->setInvoiceId($invoiceId);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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
        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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
        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
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
        $creditmemoMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $creditmemoItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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
}
