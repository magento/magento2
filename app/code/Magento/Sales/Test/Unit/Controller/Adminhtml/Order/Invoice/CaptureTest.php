<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class CaptureTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CaptureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

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
    protected $messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\Capture
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceManagement;

    /**
     * @var InvoiceRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceRepository;

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultForwardFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\ForwardFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $contextMock->expects($this->any())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $contextMock->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($this->actionFlagMock);
        $contextMock->expects($this->any())
            ->method('getHelper')
            ->willReturn($this->helperMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->invoiceManagement = $this->getMockBuilder(\Magento\Sales\Api\InvoiceManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Sales\Api\InvoiceManagementInterface::class)
            ->willReturn($this->invoiceManagement);
        $this->invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Invoice\Capture::class,
            [
                'context' => $contextMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock,
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $this->controller,
            'invoiceRepository',
            $this->invoiceRepository
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsInProcess', '__wakeup'])
            ->getMock();

        $this->invoiceManagement->expects($this->once())
            ->method('setCapture')
            ->with($invoiceId);

        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->any())
            ->method('getEntityId')
            ->will($this->returnValue($invoiceId));
        $invoiceMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));

        $transactionMock = $this->getMockBuilder(\Magento\Framework\DB\Transaction::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $transactionMock->expects($this->at(0))
            ->method('addObject')
            ->with($invoiceMock)
            ->will($this->returnSelf());
        $transactionMock->expects($this->at(1))
            ->method('addObject')
            ->with($orderMock)
            ->will($this->returnSelf());
        $transactionMock->expects($this->at(2))
            ->method('save');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with('The invoice has been captured.');

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($invoiceId));

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with(\Magento\Framework\DB\Transaction::class)
            ->will($this->returnValue($transactionMock));

        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/*/view', ['invoice_id' => $invoiceId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultRedirect));

        $this->assertSame($resultRedirect, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteNoInvoice()
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $resultForward = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultForward->expects($this->once())->method('forward')->with(('noroute'))->will($this->returnSelf());

        $this->resultForwardFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultForward));

        $this->assertSame($resultForward, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteModelException()
    {
        $invoiceId = 2;

        $message = 'Invoice capturing error';
        $e = new \Magento\Framework\Exception\LocalizedException(__($message));

        $this->invoiceManagement->expects($this->once())
            ->method('setCapture')
            ->with($invoiceId)
            ->will($this->throwException($e));

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with($message);

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($invoiceId));
        $invoiceMock->expects($this->once())
            ->method('getEntityId')
            ->will($this->returnValue($invoiceId));

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/*/view', ['invoice_id' => $invoiceId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultRedirect));

        $this->assertSame($resultRedirect, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteException()
    {
        $invoiceId = 2;

        $message = 'Invoice capturing error';
        $e = new \Exception($message);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->will($this->returnValue($invoiceId));

        $this->invoiceManagement->expects($this->once())
            ->method('setCapture')
            ->with($invoiceId)
            ->will($this->throwException($e));

        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with($message);

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($invoiceId));
        $invoiceMock->expects($this->once())
            ->method('getEntityId')
            ->will($this->returnValue($invoiceId));

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/*/view', ['invoice_id' => $invoiceId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($resultRedirect));

        $this->assertSame($resultRedirect, $this->controller->execute());
    }
}
