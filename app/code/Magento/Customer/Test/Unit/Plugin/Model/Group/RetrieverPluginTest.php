<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Plugin\Model\Group;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Group\Retriever;
use Magento\Customer\Plugin\Model\Group\RetrieverPlugin;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for RetrieverPlugin
 */
class RetrieverPluginTest extends TestCase
{
    /**
     * @var RetrieverPlugin
     */
    private $plugin;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContextMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Retriever|MockObject
     */
    private $subjectMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->subjectMock = $this->createMock(Retriever::class);
        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->plugin = new RetrieverPlugin(
            $this->userContextMock,
            $this->customerRepositoryMock,
            $this->appStateMock
        );
    }

    /**
     * Test that plugin only runs for webapi areas
     */
    public function testAroundGetCustomerGroupIdSkipsForNonWebapiArea()
    {
        $expectedResult = 5;
        $proceed = function () use ($expectedResult) {
            return $expectedResult;
        };

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('frontend');

        $this->userContextMock->expects($this->never())->method('getUserType');
        $this->loggerMock->expects($this->never())->method('info');

        $result = $this->plugin->aroundGetCustomerGroupId($this->subjectMock, $proceed);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test plugin runs for webapi_rest area with customer
     */
    public function testAroundGetCustomerGroupIdForWebapiRestAreaWithCustomer()
    {
        $customerId = 123;
        $customerGroupId = 2;

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_rest');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($customerGroupId);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customerMock);

        $proceed = function () {
            $this->fail('Proceed should not be called when API request with customer');
        };

        $result = $this->plugin->aroundGetCustomerGroupId($this->subjectMock, $proceed);

        $this->assertEquals($customerGroupId, $result);
    }

    /**
     * Test plugin runs for webapi_soap area with customer
     */
    public function testAroundGetCustomerGroupIdForWebapiSoapAreaWithCustomer()
    {
        $customerId = 456;
        $customerGroupId = 3;

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_soap');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($customerGroupId);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customerMock);

        $proceed = function () {
            $this->fail('Proceed should not be called when API request with customer');
        };

        $result = $this->plugin->aroundGetCustomerGroupId($this->subjectMock, $proceed);

        $this->assertEquals($customerGroupId, $result);
    }

    /**
     * Test guest user scenario in API
     */
    public function testAroundGetCustomerGroupIdForGuestUserInApi()
    {
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_rest');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);

        $this->userContextMock->expects($this->never())->method('getUserId');
        $this->customerRepositoryMock->expects($this->never())->method('getById');

        $proceed = function () {
            $this->fail('Proceed should not be called for guest in API');
        };

        $result = $this->plugin->aroundGetCustomerGroupId($this->subjectMock, $proceed);

        $this->assertEquals(Group::NOT_LOGGED_IN_ID, $result);
    }

    /**
     * Test admin user scenario in API
     */
    public function testAroundGetCustomerGroupIdForAdminUserInApi()
    {
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_rest');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);

        $this->userContextMock->expects($this->never())->method('getUserId');
        $this->customerRepositoryMock->expects($this->never())->method('getById');

        $proceed = function () {
            $this->fail('Proceed should not be called for admin in API');
        };

        $result = $this->plugin->aroundGetCustomerGroupId($this->subjectMock, $proceed);

        $this->assertEquals(Group::NOT_LOGGED_IN_ID, $result);
    }

    /**
     * Test customer with null ID in API
     */
    public function testAroundGetCustomerGroupIdForCustomerWithNullIdInApi()
    {
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_rest');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn(null);

        $this->customerRepositoryMock->expects($this->never())->method('getById');

        $proceed = function () {
            $this->fail('Proceed should not be called for customer with null ID');
        };

        $result = $this->plugin->aroundGetCustomerGroupId($this->subjectMock, $proceed);

        $this->assertEquals(Group::NOT_LOGGED_IN_ID, $result);
    }

    /**
     * Test NoSuchEntityException handling
     */
    public function testAroundGetCustomerGroupIdHandlesNoSuchEntityException()
    {
        $customerId = 999;
        $exception = new NoSuchEntityException(__('Customer not found'));

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_rest');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willThrowException($exception);

        $proceed = function () {
            $this->fail('Proceed should not be called when customer not found');
        };

        $result = $this->plugin->aroundGetCustomerGroupId($this->subjectMock, $proceed);

        $this->assertEquals(Group::NOT_LOGGED_IN_ID, $result);
    }

    /**
     * Test LocalizedException handling
     */
    public function testAroundGetCustomerGroupIdHandlesLocalizedException()
    {
        $customerId = 888;
        $originalResult = 10;
        $exception = new LocalizedException(__('Database error'));

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_rest');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willThrowException($exception);

        $proceed = function () use ($originalResult) {
            return $originalResult;
        };

        $result = $this->plugin->aroundGetCustomerGroupId($this->subjectMock, $proceed);

        $this->assertEquals($originalResult, $result);
    }

    /**
     * Test integer conversion of customer group ID
     */
    public function testAroundGetCustomerGroupIdConvertsToInteger()
    {
        $customerId = 123;
        $customerGroupId = '4'; // String should be converted to int

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_rest');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_CUSTOMER);

        $this->userContextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn($customerId);

        $this->customerMock->expects($this->once())
            ->method('getGroupId')
            ->willReturn($customerGroupId);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customerMock);

        $proceed = function () {
            $this->fail('Proceed should not be called');
        };

        $result = $this->plugin->aroundGetCustomerGroupId($this->subjectMock, $proceed);

        $this->assertSame(4, $result);
        $this->assertIsInt($result);
    }
}
