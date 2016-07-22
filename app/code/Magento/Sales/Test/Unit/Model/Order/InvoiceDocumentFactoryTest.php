<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceInterfaceFactory;
use Magento\Sales\Model\Order\InvoiceDocumentFactory;

/**
 * Class InvoiceDocumentFactoryTest
 */
class InvoiceDocumentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceInterfaceFactory
     */
    private $invoiceFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceInterface
     */
    private $invoiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceDocumentFactory
     */
    private $invoiceDocumentFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceCreationArgumentsInterface
     */
    private $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceCommentCreationInterface
     */
    private $commentMock;

    protected function setUp()
    {
        $this->invoiceFactoryMock = $this->getMockBuilder(InvoiceInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->invoiceMock = $this->getMockBuilder(InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemMock = $this->getMockBuilder(InvoiceItemCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->commentMock = $this->getMockBuilder(InvoiceCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceDocumentFactory = new InvoiceDocumentFactory($this->invoiceFactoryMock);
    }

    public function testCreate()
    {
        $orderId = 1;
        $this->invoiceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->invoiceMock);

        $this->assertEquals($this->invoiceMock, $this->invoiceDocumentFactory->create($orderId, [$this->itemMock], $this->commentMock));
    }
}
