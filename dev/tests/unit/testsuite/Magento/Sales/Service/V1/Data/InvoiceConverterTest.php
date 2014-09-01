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
namespace Magento\Sales\Service\V1\Data;

/**
 * Class InvoiceConverterTest
 * @package Magento\Sales\Service\V1\Data
 */
class InvoiceConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceLoaderMock;
    /**
     * @var \Magento\Sales\Service\V1\Data\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceMock;
    /**
     * @var \Magento\Sales\Service\V1\Data\InvoiceItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceItemMock;
    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelInvoiceMock;
    /**
     * @var \Magento\Sales\Service\V1\Data\InvoiceConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->invoiceLoaderMock = $this->getMockBuilder('Magento\Sales\Controller\Adminhtml\Order\InvoiceLoader')
            ->disableOriginalConstructor()
            ->setMethods(['setOrderId', 'setInvoiceId', 'setInvoiceItems', 'create'])
            ->getMock();
        $this->invoiceMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->invoiceItemMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\InvoiceItem')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->modelInvoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->converter = new \Magento\Sales\Service\V1\Data\InvoiceConverter($this->invoiceLoaderMock);
    }

    /**
     * test for Invoice converter
     */
    public function testGetModel()
    {
        $orderId = 1;
        $invoiceId = 2;
        $itemId = 3;
        $itemQty = 4;
        $this->invoiceMock->expects($this->once())
            ->method('getOrderId')
            ->will($this->returnValue($orderId));
        $this->invoiceMock->expects($this->once())
            ->method('getEntityId')
            ->will($this->returnValue($invoiceId));
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$this->invoiceItemMock]));
        $this->invoiceItemMock->expects($this->once())
            ->method('getOrderItemId')
            ->will($this->returnValue($itemId));
        $this->invoiceItemMock->expects($this->once())
            ->method('getQty')
            ->will($this->returnValue($itemQty));
        $this->invoiceLoaderMock->expects($this->once())
            ->method('setOrderId')
            ->with($this->equalTo($orderId))
            ->will($this->returnSelf());
        $this->invoiceLoaderMock->expects($this->once())
            ->method('setInvoiceId')
            ->with($this->equalTo($invoiceId))
            ->will($this->returnSelf());
        $this->invoiceLoaderMock->expects($this->once())
            ->method('setInvoiceItems')
            ->with($this->equalTo([$itemId => $itemQty]))
            ->will($this->returnSelf());
        $this->invoiceLoaderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->modelInvoiceMock));
        $this->invoiceLoaderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->modelInvoiceMock));
        $this->assertInstanceOf(
            'Magento\Sales\Model\Order\Invoice',
            $this->converter->getModel($this->invoiceMock)
        );
    }
}
