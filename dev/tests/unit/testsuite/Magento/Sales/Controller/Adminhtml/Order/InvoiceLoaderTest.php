<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

/**
 * Class InvoiceLoaderTest
 * @package Magento\Sales\Controller\Adminhtml\Order
 */
class InvoiceLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader
     */
    protected $loader;

    public function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\Manager')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->loader = new \Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader(
            $this->objectManagerMock,
            $this->registryMock,
            $this->messageManagerMock
        );
    }

    public function testLoadInvoiceId()
    {
        $orderId = 1;
        $invoiceId = 2;

        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Sales\Model\Order\Invoice')
            ->will($this->returnValue($invoiceMock));

        $this->assertFalse($this->loader->load($orderId, $invoiceId));
    }

    public function testLoadNoInvoiceId()
    {
        $orderId = 1;

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', '__wakeup'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->will($this->returnValue($orderMock));

        $this->assertFalse($this->loader->load($orderId));
    }

    public function testLoadCanNotInvoice()
    {
        $orderId = 1;

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'canInvoice', '__wakeup'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $orderMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($orderId));
        $orderMock->expects($this->once())
            ->method('canInvoice')
            ->will($this->returnValue(false));

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->will($this->returnValue($orderMock));

        $this->assertFalse($this->loader->load($orderId));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot create an invoice without products.
     */
    public function testLoadException()
    {
        $orderId = 1;

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'canInvoice', '__wakeup'])
            ->getMock();
        $orderMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $orderMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($orderId));
        $orderMock->expects($this->once())
            ->method('canInvoice')
            ->will($this->returnValue(true));

        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $serviceOrderMock = $this->getMockBuilder('Magento\Sales\Model\Service\Order')
            ->disableOriginalConstructor()
            ->setMethods(['prepareInvoice', '__wakeup'])
            ->getMock();
        $serviceOrderMock->expects($this->once())
            ->method('prepareInvoice')
            ->with([])
            ->will($this->returnValue($invoiceMock));

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Model\Order')
            ->will($this->returnValue($orderMock));
        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with('Magento\Sales\Model\Service\Order', ['order' => $orderMock])
            ->will($this->returnValue($serviceOrderMock));

        $this->assertFalse($this->loader->load($orderId));
    }

    public function testLoad()
    {
        $orderId = 1;
        $invoiceId = 2;

        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', '__wakeup'])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $invoiceMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($invoiceId));
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Sales\Model\Order\Invoice')
            ->will($this->returnValue($invoiceMock));

        $this->assertInstanceOf('Magento\Sales\Model\Order\Invoice', $this->loader->load($orderId, $invoiceId));
    }
}
