<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


/**
 * Class CreditmemoLoaderTest
 */
class CreditmemoLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $loader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceOrderFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockConfiguration;

    public function setUp()
    {
        $data = [];
        $this->creditmemoFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\CreditmemoFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create', 'get'])
            ->getMock();
        $this->orderFactoryMock = $this->getMockBuilder('Magento\Sales\Model\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->invoiceFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\InvoiceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->serviceOrderFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Service\OrderFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\Manager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->helperMock = $this->getMockBuilder('Magento\CatalogInventory\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->stockConfiguration = $this->getMockBuilder('Magento\CatalogInventory\Model\Configuration')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->loader = new \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader(
            $this->creditmemoFactoryMock,
            $this->orderFactoryMock,
            $this->invoiceFactoryMock,
            $this->serviceOrderFactoryMock,
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

        $creditmemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $creditmemoMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->creditmemoFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($creditmemoMock);

        $this->assertInstanceOf('Magento\Sales\Model\Order\Creditmemo', $this->loader->load());
    }

    public function testLoadCannotCreditmemo()
    {
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId(1);
        $this->loader->setCreditmemo('test');
        $this->loader->setInvoiceId(1);

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->willReturn(false);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $invoiceMock->expects($this->any())
            ->method('setOrder')
            ->willReturnSelf();
        $invoiceMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->invoiceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($invoiceMock);

        $this->assertFalse($this->loader->load());
    }

    public function testLoadByOrder()
    {
        $qty = 1;
        $data = ['items' => [1 => ['qty' => $qty, 'back_to_stock' => true]]];
        $this->loader->setCreditmemoId(0);
        $this->loader->setOrderId(1);
        $this->loader->setCreditmemo($data);
        $this->loader->setInvoiceId(1);

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->willReturn(true);
        $this->orderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderMock);
        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $invoiceMock->expects($this->any())
            ->method('setOrder')
            ->willReturnSelf();
        $invoiceMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->invoiceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($invoiceMock);
        $serviceOrder = $this->getMockBuilder('Magento\Sales\Model\Service\Order')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $creditmemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $creditmemoItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo\Item')
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
        $serviceOrder->expects($this->any())
            ->method('prepareInvoiceCreditmemo')
            ->willReturn($creditmemoMock);
        $this->serviceOrderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($serviceOrder);

        $this->assertInstanceOf('Magento\Sales\Model\Order\Creditmemo', $this->loader->load());
    }
}
