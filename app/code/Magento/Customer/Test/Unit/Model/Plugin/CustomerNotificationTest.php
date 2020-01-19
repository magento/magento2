<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Plugin\CustomerNotification;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class CustomerNotificationTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
    {
        $this->sessionMock = $this->createMock(Session::class);
        $this->sessionMock->method('getCustomerId')->willReturn(self::STUB_CUSTOMER_ID);

        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->actionMock = $this->createMock(ActionInterface::class);
        $this->requestMock = $this->getMockBuilder([RequestInterface::class, HttpRequestInterface::class])
            ->getMock();
        $this->requestMock->method('isPost')->willReturn(true);

        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->appStateMock = $this->createMock(State::class);
        $this->appStateMock->method('getAreaCode')->willReturn(Area::AREA_FRONTEND);

        $this->notificationStorageMock = $this->createMock(NotificationStorage::class);
        $this->notificationStorageMock->expects($this->any())
            ->method('isExists')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::STUB_CUSTOMER_ID)
            ->willReturn(true);

        $this->plugin = new CustomerNotification(
            $this->sessionMock,
            $this->notificationStorageMock,
            $this->appStateMock,
            $this->customerRepositoryMock,
            $this->loggerMock,
            $this->requestMock
        );
    }

    public function testBeforeExecute()
    {
        $customerGroupId = 1;

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->method('getGroupId')->willReturn($customerGroupId);
        $customerMock->method('getId')->willReturn(self::STUB_CUSTOMER_ID);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(self::STUB_CUSTOMER_ID)
            ->willReturn($customerMock);
        $this->notificationStorageMock->expects($this->once())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::STUB_CUSTOMER_ID);

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
}
