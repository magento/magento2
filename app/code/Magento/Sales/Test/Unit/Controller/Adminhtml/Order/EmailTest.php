<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use \Magento\Sales\Controller\Adminhtml\Order\Email;

use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class EmailTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @package Magento\Sales\Controller\Adminhtml\Order
 */
class EmailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Email
     */
    protected $orderEmail;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Sales\Api\OrderManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderManagementMock;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            [
                'getRequest',
                'getResponse',
                'getMessageManager',
                'getRedirect',
                'getObjectManager',
                'getSession',
                'getActionFlag',
                'getHelper',
                'getResultRedirectFactory'
            ],
            [],
            '',
            false
        );
        $this->orderManagementMock = $this->getMockBuilder(\Magento\Sales\Api\OrderManagementInterface::class)
            ->getMockForAbstractClass();
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Api\OrderRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        $resultRedirectFactory = $this->getMock(
            \Magento\Backend\Model\View\Result\RedirectFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->response = $this->getMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageManager = $this->getMock(
            \Magento\Framework\Message\Manager::class,
            ['addSuccess', 'addError'],
            [],
            '',
            false
        );

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->getMockForAbstractClass();
        $this->session = $this->getMock(\Magento\Backend\Model\Session::class, ['setIsUrlNotice'], [], '', false);
        $this->actionFlag = $this->getMock(\Magento\Framework\App\ActionFlag::class, ['get', 'set'], [], '', false);
        $this->helper = $this->getMock(\Magento\Backend\Helper\Data::class, ['getUrl'], [], '', false);
        $this->resultRedirect = $this->getMock(\Magento\Backend\Model\View\Result\Redirect::class, [], [], '', false);
        $resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->resultRedirect);

        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->once())->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->expects($this->once())->method('getSession')->willReturn($this->session);
        $this->context->expects($this->once())->method('getActionFlag')->willReturn($this->actionFlag);
        $this->context->expects($this->once())->method('getHelper')->willReturn($this->helper);
        $this->context->expects($this->once())->method('getResultRedirectFactory')->willReturn($resultRedirectFactory);

        $this->orderEmail = $objectManagerHelper->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Email::class,
            [
                'context' => $this->context,
                'request' => $this->request,
                'response' => $this->response,
                'orderManagement' => $this->orderManagementMock,
                'orderRepository' => $this->orderRepositoryMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testEmail()
    {
        $orderId = 10000031;
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->atLeastOnce())
            ->method('getEntityId')
            ->will($this->returnValue($orderId));
        $this->orderManagementMock->expects($this->once())
            ->method('notify')
            ->with($orderId)
            ->willReturn(true);
        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with('You sent the order email.');
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/order/view', ['order_id' => $orderId])
            ->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\Backend\Model\View\Result\Redirect::class,
            $this->orderEmail->execute()
        );
        $this->assertEquals($this->response, $this->orderEmail->getResponse());
    }

    public function testEmailNoOrderId()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue(null));
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(null)
            ->willThrowException(
                new \Magento\Framework\Exception\NoSuchEntityException(__('Requested entity doesn\'t exist'))
            );
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('This order no longer exists.');

        $this->actionFlag->expects($this->once())
            ->method('set')
            ->with('', 'no-dispatch', true)
            ->will($this->returnValue(true));
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/*/')
            ->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\Backend\Model\View\Result\Redirect::class,
            $this->orderEmail->execute()
        );
    }
}
