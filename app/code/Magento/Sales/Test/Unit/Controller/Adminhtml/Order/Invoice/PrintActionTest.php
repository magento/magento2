<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class PrintActionTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 */
class PrintActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\PrintAction
     */
    protected $controller;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->sessionMock = $this->getMockBuilder('Magento\Backend\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->actionFlagMock = $this->getMockBuilder('Magento\Framework\App\ActionFlag')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->will($this->returnValue($this->actionFlagMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));

        $this->fileFactory = $this->getMockBuilder('Magento\Framework\App\Response\Http\FileFactory')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->controller = $objectManager->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Invoice\PrintAction',
            [
                'context' => $contextMock,
                'fileFactory' => $this->fileFactory
            ]
        );
    }

    public function testExecute()
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $invoiceMock = $this->getMock('Magento\Sales\Model\Order\Invoice', [], [], '', false);

        $pdfMock = $this->getMock('Magento\Sales\Model\Order\Pdf\Invoice', ['render', 'getPdf'], [], '', false);
        $pdfMock->expects($this->once())
            ->method('getPdf')
            ->willReturnSelf();
        $pdfMock->expects($this->once())
            ->method('render');
        $dateTimeMock = $this->getMock('Magento\Framework\Stdlib\DateTime\DateTime', [], [], '', false);

        $invoiceRepository = $this->getMockBuilder('Magento\Sales\Api\InvoiceRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Api\InvoiceRepositoryInterface')
            ->willReturn($invoiceRepository);
        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with('Magento\Sales\Model\Order\Pdf\Invoice')
            ->willReturn($pdfMock);
        $this->objectManagerMock->expects($this->at(2))
            ->method('get')
            ->with('Magento\Framework\Stdlib\DateTime\DateTime')
            ->willReturn($dateTimeMock);

        $this->assertNull($this->controller->execute());
    }
}
