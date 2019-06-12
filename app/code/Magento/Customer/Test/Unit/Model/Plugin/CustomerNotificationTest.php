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
    /** @var Session|\PHPUnit_Framework_MockObject_MockObject */
<<<<<<< HEAD
    private $session;

    /** @var \Magento\Customer\Model\Customer\NotificationStorage|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationStorage;

    /** @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $customerRepository;

    /** @var State|\PHPUnit_Framework_MockObject_MockObject */
    private $appState;

    /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var AbstractAction|\PHPUnit_Framework_MockObject_MockObject */
    private $abstractAction;
=======
    private $sessionMock;

    /** @var NotificationStorage|\PHPUnit_Framework_MockObject_MockObject */
    private $notificationStorageMock;

    /** @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $customerRepositoryMock;

    /** @var State|\PHPUnit_Framework_MockObject_MockObject */
    private $appStateMock;

    /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $requestMock;

    /** @var AbstractAction|\PHPUnit_Framework_MockObject_MockObject */
    private $abstractActionMock;

    /** @var LoggerInterface */
    private $loggerMock;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    /** @var CustomerNotification */
    private $plugin;

    /** @var int */
    private static $customerId = 1;

    protected function setUp()
    {
<<<<<<< HEAD
        $this->session = $this->getMockBuilder(Session::class)
=======
        $this->sessionMock = $this->getMockBuilder(Session::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerId', 'setCustomerData', 'setCustomerGroupId', 'regenerateId'])
            ->getMock();
<<<<<<< HEAD
        $this->notificationStorage = $this->getMockBuilder(NotificationStorage::class)
=======
        $this->notificationStorageMock = $this->getMockBuilder(NotificationStorage::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->disableOriginalConstructor()
            ->setMethods(['isExists', 'remove'])
            ->getMock();
<<<<<<< HEAD
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->abstractAction = $this->getMockBuilder(AbstractAction::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->appState->method('getAreaCode')->willReturn(Area::AREA_FRONTEND);
        $this->request->method('isPost')->willReturn(true);
        $this->session->method('getCustomerId')->willReturn(self::$customerId);
        $this->notificationStorage->expects($this->any())
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->method('isExists')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::$customerId)
            ->willReturn(true);

        $this->plugin = new CustomerNotification(
<<<<<<< HEAD
            $this->session,
            $this->notificationStorage,
            $this->appState,
            $this->customerRepository,
            $this->logger
=======
            $this->sessionMock,
            $this->notificationStorageMock,
            $this->appStateMock,
            $this->customerRepositoryMock,
            $this->loggerMock
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );
    }

    public function testBeforeDispatch()
    {
        $customerGroupId =1;

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerMock->method('getGroupId')->willReturn($customerGroupId);
<<<<<<< HEAD
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with(self::$customerId)
            ->willReturn($customerMock);
        $this->session->expects($this->once())->method('setCustomerData')->with($customerMock);
        $this->session->expects($this->once())->method('setCustomerGroupId')->with($customerGroupId);
        $this->session->expects($this->once())->method('regenerateId');
        $this->notificationStorage->expects($this->once())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, self::$customerId);

        $this->plugin->beforeDispatch($this->abstractAction, $this->request);
=======
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    public function testBeforeDispatchWithNoCustomerFound()
    {
<<<<<<< HEAD
        $this->customerRepository->method('getById')
            ->with(self::$customerId)
            ->willThrowException(new NoSuchEntityException());
        $this->logger->expects($this->once())
=======
        $this->customerRepositoryMock->method('getById')
            ->with(self::$customerId)
            ->willThrowException(new NoSuchEntityException());
        $this->loggerMock->expects($this->once())
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->method('error');

        $this->plugin->beforeDispatch($this->abstractActionMock, $this->requestMock);
    }
}
