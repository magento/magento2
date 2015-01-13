<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice;

use Magento\Framework\App\Action\Context;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class EmailTest
 *
 * @package Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice
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

    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);
        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $this->messageManager = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);
        $this->session = $this->getMock('Magento\Backend\Model\Session', ['setIsUrlNotice'], [], '', false);
        $this->actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $this->helper = $this->getMock('\Magento\Backend\Helper\Data', [], [], '', false);
        $this->context->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);
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
            ->method('getHelper')
            ->willReturn($this->helper);
        $this->invoiceEmail = $objectManagerHelper->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Invoice\Email',
            [
                'context' => $this->context,
                'request' => $this->request,
                'response' => $this->response
            ]
        );
    }

    public function testEmail()
    {
        $invoiceId = 10000031;
        $orderId = 100000030;
        $invoiceClassName = 'Magento\Sales\Model\Order\Invoice';
        $cmNotifierClassName = 'Magento\Sales\Model\Order\InvoiceNotifier';
        $invoice = $this->getMock($invoiceClassName, [], [], '', false);
        $notifier = $this->getMock($cmNotifierClassName, [], [], '', false);
        $order = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $order->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with($invoiceClassName)
            ->willReturn($invoice);
        $invoice->expects($this->once())
            ->method('load')
            ->with($invoiceId)
            ->willReturnSelf();
        $invoice->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with($cmNotifierClassName)
            ->willReturn($notifier);
        $notifier->expects($this->once())
            ->method('notify')
            ->willReturn(true);
        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with('We sent the message.');

        $this->prepareRedirect($invoiceId, $orderId);

        $this->invoiceEmail->execute();
        $this->assertEquals($this->response, $this->invoiceEmail->getResponse());
    }

    public function testEmailNoInvoiceId()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn(null);
        $this->assertNull($this->invoiceEmail->execute());
    }

    public function testEmailNoInvoice()
    {
        $invoiceId = 10000031;
        $invoiceClassName = 'Magento\Sales\Model\Order\Invoice';
        $invoice = $this->getMock($invoiceClassName, [], [], '', false);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('invoice_id')
            ->willReturn($invoiceId);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with($invoiceClassName)
            ->willReturn($invoice);
        $invoice->expects($this->once())
            ->method('load')
            ->with($invoiceId)
            ->willReturn(null);

        $this->assertNull($this->invoiceEmail->execute());
    }

    /***
     * @param $invoiceId
     * @param $orderId
     */
    protected function prepareRedirect($invoiceId, $orderId)
    {
        $this->actionFlag->expects($this->once())
            ->method('get')
            ->with('', 'check_url_settings')
            ->willReturn(true);
        $this->session->expects($this->once())
            ->method('setIsUrlNotice')
            ->with(true);
        $path = 'sales/invoice/view';
        $this->response->expects($this->once())
            ->method('setRedirect')
            ->with($path . '/' . $invoiceId);
        $this->helper->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with($path, ['order_id' => $orderId, 'invoice_id' => $invoiceId])
            ->willReturn($path . '/' . $invoiceId);
    }
}
