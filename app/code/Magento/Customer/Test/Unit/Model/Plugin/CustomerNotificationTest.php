<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\Plugin\CustomerNotification;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class CustomerNotificationTest extends \PHPUnit\Framework\TestCase
{
    /** @var Session|\PHPUnit\Framework\MockObject\MockObject */
    private $sessionMock;

    /** @var NotificationStorage|\PHPUnit\Framework\MockObject\MockObject */
    private $notificationStorageMock;

    /** @var CustomerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $customerRepositoryMock;

    /** @var State|\PHPUnit\Framework\MockObject\MockObject */
    private $appStateMock;

    /** @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $requestMock;

    /** @var AbstractAction|\PHPUnit\Framework\MockObject\MockObject */
    private $abstractActionMock;

    /** @var LoggerInterface */
    private $loggerMock;

    /** @var CustomerNotification */
    private $plugin;

    /** @var int */
    private static $customerId = 1;

    protected function setUp(): void
    {
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'setCustomerData', 'setCustomerGroupId', 'regenerateId'])
            ->getMock();
        $this->notificationStorageMock = $this->getMockBuilder(NotificationStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['isExists', 'remove'])
            ->getMock();
        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->abstractActionMock = $this->getMockBuilder(AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->appStateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAreaCode'])
            ->getMock();

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->appStateMock->method('getAreaCode')->willReturn(Area::AREA_FRONTEND);
        $this->requestMock->method('isPost')->willReturn(true);
        $this->sessionMock->method('getCustomerId')->willReturn(self::$customerId);
        $this->notificationStorageMock->expects($this->any())
            ->method('isExists')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::$customerId)
            ->willReturn(true);

        $this->plugin = new CustomerNotification(
            $this->sessionMock,
            $this->notificationStorageMock,
            $this->appStateMock,
            $this->customerRepositoryMock,
            $this->loggerMock
        );
    }

    public function testBeforeDispatch()
    {
        $customerGroupId =1;

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock->method('getGroupId')->willReturn($customerGroupId);
        $customerMock->method('getId')->willReturn(self::$customerId);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with(self::$customerId)
            ->willReturn($customerMock);
        $this->notificationStorageMock->expects($this->once())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::$customerId);

        $this->sessionMock->expects($this->once())->method('setCustomerData')->with($customerMock);
        $this->sessionMock->expects($this->once())->method('setCustomerGroupId')->with($customerGroupId);
        $this->sessionMock->expects($this->once())->method('regenerateId');

        $this->plugin->beforeDispatch($this->abstractActionMock, $this->requestMock);
    }

    public function testBeforeDispatchWithNoCustomerFound()
    {
        $this->customerRepositoryMock->method('getById')
            ->with(self::$customerId)
            ->willThrowException(new NoSuchEntityException());
        $this->loggerMock->expects($this->once())
            ->method('error');

        $this->plugin->beforeDispatch($this->abstractActionMock, $this->requestMock);
    }
}
