<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
class EmailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Email
     */
    protected $invoiceEmail;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlag;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForward;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceManagement;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $this->messageManager = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);
        $this->session = $this->getMock('Magento\Backend\Model\Session', ['setIsUrlNotice'], [], '', false);
        $this->actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $this->helper = $this->getMock('\Magento\Backend\Helper\Data', [], [], '', false);
        $this->resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
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

        $this->invoiceManagement = $this->getMockBuilder('Magento\Sales\Api\InvoiceManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForward = $this->getMockBuilder('Magento\Backend\Model\View\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\ForwardFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->invoiceEmail = $objectManagerHelper->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Invoice\Email',
            [
                'context' => $this->context,
                'resultForwardFactory' => $this->resultForwardFactory,
            ]
        );
    }

    public function testEmail()
    {
        $invoiceId = 10000031;
        $orderId = 100000030;
        $invoiceClassName = 'Magento\Sales\Model\Order\Invoice';
        $cmNotifierClassName = 'Magento\Sales\Api\InvoiceManagementInterface';
        $invoice = $this->getMock($invoiceClassName, [], [], '', false);
        $invoice->expects($this->once())
            ->method('getEntityId')
            ->willReturn($invoiceId);
        $order = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $order->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);
        $invoiceRepository = $this->getMockBuilder('Magento\Sales\Api\InvoiceRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn($invoice);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Api\InvoiceRepositoryInterface')
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
            ->method('addSuccess')
            ->with('You sent the message.');

        $this->resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/invoice/view', ['order_id' => $orderId, 'invoice_id' => $invoiceId])
            ->willReturnSelf();
        $this->assertInstanceOf('Magento\Backend\Model\View\Result\Redirect', $this->invoiceEmail->execute());
    }

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

        $this->assertInstanceOf('Magento\Backend\Model\View\Result\Forward', $this->invoiceEmail->execute());
    }

    public function testEmailNoInvoice()
    {
        $invoiceId = 10000031;
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);

        $invoiceRepository = $this->getMockBuilder('Magento\Sales\Api\InvoiceRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $invoiceRepository->expects($this->any())
            ->method('get')
            ->willReturn(null);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with('Magento\Sales\Api\InvoiceRepositoryInterface')
            ->willReturn($invoiceRepository);

        $this->resultForwardFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->resultForward);
        $this->resultForward->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertInstanceOf('Magento\Backend\Model\View\Result\Forward', $this->invoiceEmail->execute());
    }
}
