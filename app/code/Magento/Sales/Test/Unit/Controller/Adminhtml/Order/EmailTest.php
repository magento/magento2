<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Email;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends TestCase
{
    /**
     * @var Email
     */
    protected $orderEmail;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var Http|MockObject
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
     * @var OrderManagementInterface|MockObject
     */
    protected $orderManagementMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var OrderInterface|MockObject
     */
    protected $orderMock;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->createPartialMock(Context::class, [
            'getRequest',
            'getResponse',
            'getMessageManager',
            'getRedirect',
            'getObjectManager',
            'getSession',
            'getActionFlag',
            'getHelper',
            'getResultRedirectFactory'
        ]);
        $this->orderManagementMock = $this->getMockBuilder(OrderManagementInterface::class)
            ->getMockForAbstractClass();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->createPartialMock(
            Manager::class,
            ['addSuccessMessage', 'addErrorMessage']
        );

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->getMockForAbstractClass();
        $this->session = $this->getMockBuilder(Session::class)
            ->addMethods(['setIsUrlNotice'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlag = $this->createPartialMock(ActionFlag::class, ['get', 'set']);
        $this->helper = $this->createPartialMock(Data::class, ['getUrl']);
        $this->resultRedirect = $this->createMock(Redirect::class);
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
            Email::class,
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

    /**
     * testEmail
     */
    public function testEmail()
    {
        $orderId = 10000031;
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderId);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->atLeastOnce())
            ->method('getEntityId')
            ->willReturn($orderId);
        $this->orderManagementMock->expects($this->once())
            ->method('notify')
            ->with($orderId)
            ->willReturn(true);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You sent the order email.');
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/order/view', ['order_id' => $orderId])
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->orderEmail->execute()
        );
        $this->assertEquals($this->response, $this->orderEmail->getResponse());
    }

    /**
     * testEmailNoOrderId
     */
    public function testEmailNoOrderId()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('order_id')
            ->willReturn(null);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with(null)
            ->willThrowException(
                new NoSuchEntityException(
                    __("The entity that was requested doesn't exist. Verify the entity and try again.")
                )
            );
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('This order no longer exists.');

        $this->actionFlag->expects($this->once())
            ->method('set')
            ->with('', 'no-dispatch', true)
            ->willReturn(true);
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/*/')
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->orderEmail->execute()
        );
    }
}
