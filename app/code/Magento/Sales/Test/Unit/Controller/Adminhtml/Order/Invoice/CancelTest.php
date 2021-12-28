<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\Cancel;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CancelTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $responseMock;

    /**
     * @var MockObject
     */
    protected $messageManagerMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $actionFlagMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var Cancel
     */
    protected $controller;

    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    protected $invoiceRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->messageManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->actionFlagMock = $this->getMockBuilder(ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
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
            Cancel::class,
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
    public function testExecute(): void
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->addMethods(['setIsInProcess'])
            ->getMock();

        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('cancel');
        $invoiceMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);

        $transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transactionMock->method('addObject')
            ->withConsecutive([$invoiceMock], [$orderMock])
            ->willReturnOnConsecutiveCalls($transactionMock, $transactionMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You canceled the invoice.');

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Transaction::class)
            ->willReturn($transactionMock);

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn($invoiceId);

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/*/view', ['invoice_id' => $invoiceId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);

        $this->assertSame($resultRedirect, $this->controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteNoInvoice(): void
    {
        $invoiceId = 2;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $resultForward = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
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
    public function testExecuteModelException(): void
    {
        $invoiceId = 2;

        $message = 'model exception';
        $e = new LocalizedException(__($message));

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('cancel')
            ->willThrowException($e);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($message);

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn($invoiceId);

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
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
    public function testExecuteException(): void
    {
        $invoiceId = 2;

        $message = 'Invoice canceling error';
        $e = new \Exception($message);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceMock->expects($this->once())
            ->method('cancel')
            ->willThrowException($e);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($message);

        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn($invoiceId);

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/*/view', ['invoice_id' => $invoiceId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);

        $this->assertSame($resultRedirect, $this->controller->execute());
    }
}
