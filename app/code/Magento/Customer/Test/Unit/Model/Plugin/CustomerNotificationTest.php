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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class CustomerNotificationTest extends \PHPUnit\Framework\TestCase
{
    /** @var Session|\PHPUnit_Framework_MockObject_MockObject */
    private $session;

    /** @var NotificationStorage|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationStorage;

    /** @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $customerRepository;

    /** @var State|\PHPUnit_Framework_MockObject_MockObject */
    private $appState;

    /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var ActionInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $actionInterfaceMock;
    
    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /** @var CustomerNotification */
    private $plugin;

    /** @var int */
    private static $customerId = 1;

    protected function setUp()
    {
        $this->session = $this->createMock(Session::class);
        $this->notificationStorage = $this->createMock(NotificationStorage::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->actionInterfaceMock = $this->createMock(ActionInterface::class);
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->appState = $this->createMock(State::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->appState->method('getAreaCode')->willReturn(Area::AREA_FRONTEND);
        $this->request->method('isPost')->willReturn(true);
        $this->session->method('getCustomerId')->willReturn(self::$customerId);
        $this->notificationStorage->expects($this->any())
            ->method('isExists')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::$customerId)
            ->willReturn(true);

        $this->plugin = new CustomerNotification(
            $this->session,
            $this->notificationStorage,
            $this->appState,
            $this->customerRepository,
            $this->logger,
            $this->request
        );
    }

    public function testBeforeExecute()
    {
        $customerGroupId =1;

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->method('getGroupId')->willReturn($customerGroupId);
        $customerMock->method('getId')->willReturn(self::$customerId);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with(self::$customerId)
            ->willReturn($customerMock);
        $this->notificationStorage->expects($this->once())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::$customerId);

        $this->plugin->beforeExecute($this->actionInterfaceMock);
    }

    public function testBeforeDispatchWithNoCustomerFound()
    {
        $this->customerRepository->method('getById')
            ->with(self::$customerId)
            ->willThrowException(new NoSuchEntityException());
        $this->logger->expects($this->once())
            ->method('error');

        $this->plugin->beforeExecute($this->actionInterfaceMock);
    }
}
