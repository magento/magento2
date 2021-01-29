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
 * Class CancelTest
 * @package Magento\Sales\Controller\Adminhtml\Order\Invoice
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionFlagMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Invoice\Cancel
     */
    protected $controller;

    /**
     * @var InvoiceRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $invoiceRepository;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->actionFlagMock = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->helperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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

        $this->invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->controller = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Invoice\Cancel::class,
            [
                'context' => $contextMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock
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
            ->willReturn($invoiceId);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsInProcess', '__wakeup'])
            ->getMock();

        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('cancel');
        $invoiceMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);

        $transactionMock = $this->getMockBuilder(\Magento\Framework\DB\Transaction::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $transactionMock->expects($this->at(0))
            ->method('addObject')
            ->with($invoiceMock)
            ->willReturnSelf();
        $transactionMock->expects($this->at(1))
            ->method('addObject')
            ->with($orderMock)
            ->willReturnSelf();
        $transactionMock->expects($this->at(2))
            ->method('save');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You canceled the invoice.');

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\DB\Transaction::class)
            ->willReturn($transactionMock);

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn($invoiceId);

        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/*/view', ['invoice_id' => $invoiceId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);

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
            ->willReturn($invoiceId);

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $resultForward = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $resultForward->expects($this->once())->method('forward')->with(('noroute'))->willReturnSelf();

        $this->resultForwardFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultForward);

        $this->assertSame($resultForward, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteModelException()
    {
        $invoiceId = 2;

        $message = 'model exception';
        $e = new \Magento\Framework\Exception\LocalizedException(__($message));

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('cancel')
            ->will($this->throwException($e));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($message);

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn($invoiceId);

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
            ->willReturn($resultRedirect);

        $this->assertSame($resultRedirect, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteException()
    {
        $invoiceId = 2;

        $message = 'Invoice canceling error';
        $e = new \Exception($message);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('cancel')
            ->will($this->throwException($e));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($message);

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn($invoiceId);

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
            ->willReturn($resultRedirect);

        $this->assertSame($resultRedirect, $this->controller->execute());
    }
}
