<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Plugin\CustomerNotification;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\StorageInterface;
use Magento\Framework\App\Request\Http as RequestHttp;

/**
 * Unit test for CustomerNotification plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerNotificationTest extends TestCase
{
    private const STUB_CUSTOMER_ID = 1;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var NotificationStorage|MockObject
     */
    private $notificationStorageMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var ActionInterface|MockObject
     */
    private $actionMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var CustomerNotification
     */
    private $plugin;

    /**
     * @var StorageInterface|MockObject
     */
    private $storage;

    protected function setUp(): void
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->sessionMock->method('getCustomerId')->willReturn(self::STUB_CUSTOMER_ID);

        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $this->requestMock = $this->createMock(RequestHttp::class);
        $this->requestMock->method('isPost')->willReturn(true);

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->appStateMock = $this->createMock(State::class);
        $this->appStateMock->method('getAreaCode')->willReturn(Area::AREA_FRONTEND);

        $this->notificationStorageMock = $this->createMock(NotificationStorage::class);
        $this->notificationStorageMock->expects($this->any())
            ->method('isExists')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::STUB_CUSTOMER_ID)
            ->willReturn(true);

        $this->storage = $this
            ->getMockBuilder(StorageInterface::class)
            ->addMethods(['getData', 'setData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->plugin = new CustomerNotification(
            $this->sessionMock,
            $this->notificationStorageMock,
            $this->appStateMock,
            $this->customerRepositoryMock,
            $this->loggerMock,
            $this->requestMock,
            $this->storage
        );
    }

    public function testBeforeExecute()
    {
        $customerGroupId = 1;
        $testSessionId = [uniqid()];

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock->method('getGroupId')->willReturn($customerGroupId);
        $customerMock->method('getId')->willReturn(self::STUB_CUSTOMER_ID);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturn($customerMock);
        $this->notificationStorageMock->expects($this->once())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::STUB_CUSTOMER_ID);

        $this->sessionMock->expects($this->once())->method('setCustomerData')->with($customerMock);
        $this->sessionMock->expects($this->once())->method('setCustomerGroupId')->with($customerGroupId);
        $this->sessionMock->expects($this->once())->method('regenerateId');
        $this->storage->expects($this->once())->method('getData')->willReturn($testSessionId);
        $this->storage
            ->expects($this->once())
            ->method('setData');

        $this->plugin->beforeExecute($this->actionMock);
    }

    public function testBeforeDispatchWithNoCustomerFound()
    {
        $this->customerRepositoryMock->method('getById')
            ->with(self::STUB_CUSTOMER_ID)
            ->willThrowException(new NoSuchEntityException());
        $this->loggerMock->expects($this->once())
            ->method('error');

        $this->plugin->beforeExecute($this->actionMock);
    }

    public function testBeforeExecuteForLogoutRequest()
    {
        $this->requestMock->method('getRouteName')->willReturn('customer');
        $this->requestMock->method('getControllerName')->willReturn('account');
        $this->requestMock->method('getActionName')->willReturn('logout');

        $this->sessionMock->expects($this->never())->method('regenerateId');
        $this->sessionMock->expects($this->never())->method('setCustomerData');
        $this->sessionMock->expects($this->never())->method('setCustomerGroupId');

        $this->plugin->beforeExecute($this->actionMock);
    }
}
