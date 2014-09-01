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
namespace Magento\Sales\Service\V1\Action;

/**
 * Class InvoiceCreateTest
 */
class InvoiceCreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\InvoiceCreate
     */
    protected $invoiceCreate;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    public function setUp()
    {
        $this->invoiceConverterMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\InvoiceConverter')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('Magento\Framework\Logger')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->invoiceCreate = new InvoiceCreate(
            $this->invoiceConverterMock,
            $this->loggerMock
        );
    }

    public function testInvoke()
    {
        $invoiceMock = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('register');
        $invoiceMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));
        $invoiceDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->invoiceConverterMock->expects($this->once())
            ->method('getModel')
            ->with($invoiceDataObjectMock)
            ->will($this->returnValue($invoiceMock));
        $this->assertTrue($this->invoiceCreate->invoke($invoiceDataObjectMock));
    }

    public function testInvokeNoInvoice()
    {
        $invoiceDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->invoiceConverterMock->expects($this->once())
            ->method('getModel')
            ->with($invoiceDataObjectMock)
            ->will($this->returnValue(false));
        $this->assertFalse($this->invoiceCreate->invoke($invoiceDataObjectMock));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An error has occurred during creating Invoice
     */
    public function testInvokeException()
    {
        $message = 'Can not save Invoice';
        $e = new \Exception($message);

        $invoiceDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Invoice')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loggerMock->expects($this->once())
            ->method('logException')
            ->with($e);
        $this->invoiceConverterMock->expects($this->once())
            ->method('getModel')
            ->with($invoiceDataObjectMock)
            ->will($this->throwException($e));
        $this->invoiceCreate->invoke($invoiceDataObjectMock);
    }
}
