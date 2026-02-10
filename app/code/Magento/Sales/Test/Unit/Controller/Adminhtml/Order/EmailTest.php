<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends TestCase
{
    use MockCreationTrait;

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
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $this->response = $this->createPartialMockWithReflection(
            ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );
        $this->request = $this->createMock(Http::class);
        $this->messageManager = $this->createPartialMock(
            Manager::class,
            ['addSuccessMessage', 'addErrorMessage', 'addWarningMessage']
        );

        $this->orderMock = $this->createPartialMockWithReflection(
            Order::class,
            ['getEntityId', 'getStore', 'getStoreId']
        );
        $this->session = $this->createPartialMockWithReflection(Session::class, ['setIsUrlNotice']);
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

        $store = $this->createMock(Store::class);
        $store->method('getConfig')->willReturnMap([
            ['sales_email/order/enabled', 1],
            ['sales_email/general/async_sending', 0],
        ]);
        $this->orderMock->method('getStore')->willReturn($store);

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

    public function testEmailDisabledConfig(): void
    {
        $orderId = 10000031;

        $store = $this->createMock(Store::class);
        $store->method('getConfig')->willReturnCallback(function ($path) {
            return match ($path) {
                'sales_email/order/enabled' => false,
                'sales_email/general/async_sending' => false,
                default => null
            };
        });
        $this->orderMock->method('getStore')->willReturn($store);

        $this->request->expects($this->once())
            ->method('getParam')->with('order_id')->willReturn($orderId);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')->with($orderId)->willReturn($this->orderMock);
        $this->orderMock->method('getStore')->willReturn($store);
        $this->orderMock->method('getStoreId')->willReturn(1);
        $this->orderMock->method('getEntityId')->willReturn($orderId);

        $this->orderManagementMock->expects($this->never())->method('notify');

        $this->messageManager->expects($this->once())
            ->method('addWarningMessage')
            ->with('Order emails are disabled for this store. No email was sent.');

        $this->resultRedirect->expects($this->once())
            ->method('setPath')->with('sales/order/view', ['order_id' => $orderId])->willReturnSelf();

        $this->assertInstanceOf(Redirect::class, $this->orderEmail->execute());
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

    /**
     * Test LocalizedException is caught and proper error message is displayed
     *
     * @return void
     */
    public function testEmailWithLocalizedException(): void
    {
        $orderId = 10000031;
        $exceptionMessage = 'Localized exception message';

        $store = $this->createMock(Store::class);
        $store->method('getConfig')->willReturnMap([
            ['sales_email/order/enabled', 1],
            ['sales_email/general/async_sending', 0],
        ]);
        $this->orderMock->method('getStore')->willReturn($store);

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

        // Simulate LocalizedException being thrown during notify
        $this->orderManagementMock->expects($this->once())
            ->method('notify')
            ->with($orderId)
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage))
            );

        // Verify the exception message is added as error
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with($exceptionMessage);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/order/view', ['order_id' => $orderId])
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->orderEmail->execute()
        );
    }

    /**
     * Test generic Exception is caught, generic error message is displayed, and exception is logged
     *
     * @return void
     */
    public function testEmailWithGenericException(): void
    {
        $orderId = 10000031;
        $exception = new \Exception('Some unexpected error');

        $store = $this->createMock(Store::class);
        $store->method('getConfig')->willReturnMap([
            ['sales_email/order/enabled', 1],
            ['sales_email/general/async_sending', 0],
        ]);
        $this->orderMock->method('getStore')->willReturn($store);

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

        // Simulate generic Exception being thrown during notify
        $this->orderManagementMock->expects($this->once())
            ->method('notify')
            ->with($orderId)
            ->willThrowException($exception);

        // Verify generic error message is added
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with('We can\'t send the email order right now.');

        // Verify the exception is logged
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/order/view', ['order_id' => $orderId])
            ->willReturnSelf();

        $this->assertInstanceOf(
            Redirect::class,
            $this->orderEmail->execute()
        );
    }
}
