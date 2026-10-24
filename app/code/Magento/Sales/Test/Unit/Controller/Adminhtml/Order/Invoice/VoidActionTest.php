<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\UpdateQty;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\VoidAction;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VoidActionTest extends TestCase
{
    use MockCreationTrait;

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
    protected $objectManagerMock;

    /**
     * @var MockObject
     */
    protected $messageManagerMock;

    /**
     * @var MockObject
     */
    protected $actionFlagMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var UpdateQty
     */
    protected $controller;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var MockObject
     */
    protected $invoiceManagement;

    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    protected $invoiceRepository;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->createMock(Http::class);
        $this->responseMock = $this->createMock(ResponseHttp::class);

        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->messageManagerMock = $this->createMock(Manager::class);

        $this->actionFlagMock = $this->createMock(ActionFlag::class);

        $this->helperMock = $this->createMock(Data::class);

        $this->sessionMock = $this->createMock(Session::class);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->invoiceManagement = $this->createMock(InvoiceManagementInterface::class);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(InvoiceManagementInterface::class)
            ->willReturn($this->invoiceManagement);

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getRequest',
                    'getResponse',
                    'getObjectManager',
                    'getSession',
                    'getHelper',
                    'getActionFlag',
                    'getMessageManager',
                    'getResultRedirectFactory'
                ]
            )
            ->getMock();
        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $contextMock->expects($this->any())->method('getActionFlag')->willReturn($this->actionFlagMock);
        $contextMock->expects($this->any())->method('getSession')->willReturn($this->sessionMock);
        $contextMock->expects($this->any())->method('getHelper')->willReturn($this->helperMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);

        $this->controller = $objectManager->getObject(
            VoidAction::class,
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

        $orderMock = $this->createPartialMockWithReflection(Order::class, ['setIsInProcess']);

        $this->invoiceManagement->expects($this->once())
            ->method('setVoid')
            ->with($invoiceId)
            ->willReturn(true);

        $invoiceMock = $this->createMock(Invoice::class);
        $invoiceMock->expects($this->any())
            ->method('getEntityId')
            ->willReturn($invoiceId);
        $invoiceMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);
        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn($invoiceId);

        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->method('addObject')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$invoiceMock] => $transactionMock,
                [$orderMock] => $transactionMock
            });

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->objectManagerMock
            ->method('create')
            ->with(Transaction::class)
            ->willReturn($transactionMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The invoice has been voided.');

        $resultRedirect = $this->createMock(Redirect::class);
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/*/view', ['invoice_id' => $invoiceId]);

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

        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage');
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $resultForward = $this->createMock(Forward::class);
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
        $message = 'test message';
        $e = new LocalizedException(__($message));

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $this->invoiceManagement->expects($this->once())
            ->method('setVoid')
            ->with($invoiceId)
            ->willThrowException($e);

        $invoiceMock = $this->createMock(Invoice::class);
        $invoiceMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn($invoiceId);
        $invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn($invoiceId);

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->willReturn($invoiceMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage');

        $resultRedirect = $this->createMock(Redirect::class);
        $resultRedirect->expects($this->once())->method('setPath')->with('sales/*/view', ['invoice_id' => $invoiceId]);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirect);

        $this->assertSame($resultRedirect, $this->controller->execute());
    }
}
