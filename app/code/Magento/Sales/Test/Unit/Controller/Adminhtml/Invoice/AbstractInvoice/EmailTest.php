<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Invoice\AbstractInvoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\Email;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\Email as OrderInvoiceEmail;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends TestCase
{
    /**
     * @var Email
     */
    protected $invoiceEmail;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var Manager|MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|MockObject
     */
    protected $objectManager;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var ActionFlag|MockObject
     */
    protected $actionFlag;

    /**
     * @var Data|MockObject
     */
    protected $helper;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var Forward|MockObject
     */
    protected $resultForward;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactory;

    /**
     * @var MockObject
     */
    protected $invoiceManagement;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->createMock(Context::class);
        $this->response = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $this->messageManager = $this->createMock(Manager::class);
        $this->session = $this->getMockBuilder(Session::class)
            ->addMethods(['setIsUrlNotice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlag = $this->createMock(ActionFlag::class);
        $this->helper = $this->createMock(Data::class);
        $this->resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->context->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);
        $this->context->expects($this->once())
            ->method('getSession')
            ->willReturn($this->session);
        $this->context->expects($this->once())
            ->method('getActionFlag')
            ->willReturn($this->actionFlag);
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->context->expects($this->once())
            ->method('getHelper')
            ->willReturn($this->helper);
        $this->context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->invoiceManagement = $this->getMockBuilder(InvoiceManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultForward = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactory = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->invoiceEmail = $objectManagerHelper->getObject(
            OrderInvoiceEmail::class,
            [
                'context' => $this->context,
                'resultForwardFactory' => $this->resultForwardFactory,
            ]
        );
    }

    /**
     * testEmail
     */
    public function testEmail()
    {
        $invoiceId = 10000031;
        $orderId = 100000030;
        $invoiceClassName = Invoice::class;
        $cmNotifierClassName = InvoiceManagementInterface::class;
        $invoice = $this->createMock($invoiceClassName);
        $invoice->expects($this->once())
            ->method('getEntityId')
            ->willReturn($invoiceId);
        $order = $this->createMock(Order::class);
        $order->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);
        $invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn($invoice);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with(InvoiceRepositoryInterface::class)
            ->willReturn($invoiceRepository);

        $invoice->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with($cmNotifierClassName)
            ->willReturn($this->invoiceManagement);

        $this->invoiceManagement->expects($this->once())
            ->method('notify')
            ->with($invoiceId)
            ->willReturn(true);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You sent the message.');

        $this->resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/invoice/view', ['order_id' => $orderId, 'invoice_id' => $invoiceId])
            ->willReturnSelf();
        $this->assertInstanceOf(Redirect::class, $this->invoiceEmail->execute());
    }

    /**
     * testEmailNoInvoiceId
     */
    public function testEmailNoInvoiceId()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn(null);
        $this->resultForwardFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultForward);
        $this->resultForward->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertInstanceOf(Forward::class, $this->invoiceEmail->execute());
    }

    /**
     * testEmailNoInvoice
     */
    public function testEmailNoInvoice()
    {
        $invoiceId = 10000031;
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn(null);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with(InvoiceRepositoryInterface::class)
            ->willReturn($invoiceRepository);

        $this->resultForwardFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultForward);
        $this->resultForward->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertInstanceOf(Forward::class, $this->invoiceEmail->execute());
    }
}
