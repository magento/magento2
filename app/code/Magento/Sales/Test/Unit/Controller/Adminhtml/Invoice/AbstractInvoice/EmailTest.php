<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Invoice\AbstractInvoice;

use \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\Email;

use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class EmailTest
 *
 * @package Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Email
     */
    protected $invoiceEmail;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionFlag;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForward;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForwardFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $invoiceManagement;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->session = $this->createPartialMock(\Magento\Backend\Model\Session::class, ['setIsUrlNotice']);
        $this->actionFlag = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $this->helper = $this->createMock(\Magento\Backend\Helper\Data::class);
        $this->resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
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

        $this->invoiceManagement = $this->getMockBuilder(\Magento\Sales\Api\InvoiceManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForward = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->invoiceEmail = $objectManagerHelper->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Invoice\Email::class,
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
        $invoiceClassName = \Magento\Sales\Model\Order\Invoice::class;
        $cmNotifierClassName = \Magento\Sales\Api\InvoiceManagementInterface::class;
        $invoice = $this->createMock($invoiceClassName);
        $invoice->expects($this->once())
            ->method('getEntityId')
            ->willReturn($invoiceId);
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);
        $invoiceRepository = $this->getMockBuilder(\Magento\Sales\Api\InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn($invoice);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with(\Magento\Sales\Api\InvoiceRepositoryInterface::class)
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
        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Redirect::class, $this->invoiceEmail->execute());
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

        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Forward::class, $this->invoiceEmail->execute());
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

        $invoiceRepository = $this->getMockBuilder(\Magento\Sales\Api\InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn(null);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with(\Magento\Sales\Api\InvoiceRepositoryInterface::class)
            ->willReturn($invoiceRepository);

        $this->resultForwardFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultForward);
        $this->resultForward->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Forward::class, $this->invoiceEmail->execute());
    }
}
