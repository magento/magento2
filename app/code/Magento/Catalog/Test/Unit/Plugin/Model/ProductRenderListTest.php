<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Model\ProductRenderList;
use Magento\Catalog\Plugin\Model\ProductRenderListPlugin;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\Group;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for ProductRenderListPlugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRenderListTest extends TestCase
{
    /**
     * @var ProductRenderListPlugin
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
     * @var HttpContext|MockObject
     */
    private $httpContextMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ProductRenderList|MockObject
     */
    private $subjectMock;

    /**
     * @var SearchCriteriaInterface|MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    protected function setUp(): void
    {
        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->httpContextMock = $this->createMock(HttpContext::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->subjectMock = $this->createMock(ProductRenderList::class);
        $this->searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->plugin = new ProductRenderListPlugin(
            $this->userContextMock,
            $this->customerRepositoryMock,
            $this->httpContextMock,
            $this->appStateMock,
            $this->loggerMock
        );
    }

    /**
     * Test that plugin only runs for webapi areas
     */
    public function testBeforeGetListSkipsForNonWebapiArea()
    {
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('frontend');

        $this->userContextMock->expects($this->never())->method('getUserType');
        $this->httpContextMock->expects($this->never())->method('setValue');
        $this->loggerMock->expects($this->never())->method('info');

        $result = $this->plugin->beforeGetList($this->subjectMock, $this->searchCriteriaMock, 1, 'USD');

        $this->assertEquals([$this->searchCriteriaMock, 1, 'USD'], $result);
    }

    /**
     * Test plugin runs for webapi_rest area
     */
    public function testBeforeGetListRunsForWebapiRestArea()
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

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(
                Context::CONTEXT_GROUP,
                (string)$customerGroupId,
                Group::NOT_LOGGED_IN_ID
            );

        $result = $this->plugin->beforeGetList($this->subjectMock, $this->searchCriteriaMock, 1, 'USD');

        $this->assertEquals([$this->searchCriteriaMock, 1, 'USD'], $result);
    }

    /**
     * Test plugin runs for webapi_soap area
     */
    public function testBeforeGetListRunsForWebapiSoapArea()
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

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(
                Context::CONTEXT_GROUP,
                (string)$customerGroupId,
                Group::NOT_LOGGED_IN_ID
            );

        $result = $this->plugin->beforeGetList($this->subjectMock, $this->searchCriteriaMock, 2, 'EUR');

        $this->assertEquals([$this->searchCriteriaMock, 2, 'EUR'], $result);
    }

    /**
     * Test guest user scenario
     */
    public function testBeforeGetListForGuestUser()
    {
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_rest');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_GUEST);

        $this->userContextMock->expects($this->never())->method('getUserId');
        $this->customerRepositoryMock->expects($this->never())->method('getById');

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(
                Context::CONTEXT_GROUP,
                (string)Group::NOT_LOGGED_IN_ID,
                Group::NOT_LOGGED_IN_ID
            );

        $result = $this->plugin->beforeGetList($this->subjectMock, $this->searchCriteriaMock);

        $this->assertEquals([$this->searchCriteriaMock, null, null], $result);
    }

    /**
     * Test admin user scenario
     */
    public function testBeforeGetListForAdminUser()
    {
        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('webapi_rest');

        $this->userContextMock->expects($this->once())
            ->method('getUserType')
            ->willReturn(UserContextInterface::USER_TYPE_ADMIN);

        $this->userContextMock->expects($this->never())->method('getUserId');
        $this->customerRepositoryMock->expects($this->never())->method('getById');

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(
                Context::CONTEXT_GROUP,
                (string)Group::NOT_LOGGED_IN_ID,
                Group::NOT_LOGGED_IN_ID
            );

        $result = $this->plugin->beforeGetList($this->subjectMock, $this->searchCriteriaMock);

        $this->assertEquals([$this->searchCriteriaMock, null, null], $result);
    }

    /**
     * Test customer with null ID
     */
    public function testBeforeGetListForCustomerWithNullId()
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

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(
                Context::CONTEXT_GROUP,
                (string)Group::NOT_LOGGED_IN_ID,
                Group::NOT_LOGGED_IN_ID
            );

        $result = $this->plugin->beforeGetList($this->subjectMock, $this->searchCriteriaMock);

        $this->assertEquals([$this->searchCriteriaMock, null, null], $result);
    }

    /**
     * Test NoSuchEntityException handling
     */
    public function testBeforeGetListHandlesNoSuchEntityException()
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

        $this->httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(
                Context::CONTEXT_GROUP,
                (string)Group::NOT_LOGGED_IN_ID,
                Group::NOT_LOGGED_IN_ID
            );

        $result = $this->plugin->beforeGetList($this->subjectMock, $this->searchCriteriaMock);

        $this->assertEquals([$this->searchCriteriaMock, null, null], $result);
    }

    /**
     * Test general exception handling
     */
    public function testBeforeGetListHandlesGeneralException()
    {
        $exception = new \Exception('Unexpected error');

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                'Error in ProductRenderList plugin: Unexpected error',
                ['exception' => $exception]
            );

        $this->httpContextMock->expects($this->never())->method('setValue');
        $this->userContextMock->expects($this->never())->method('getUserType');

        $result = $this->plugin->beforeGetList($this->subjectMock, $this->searchCriteriaMock);

        $this->assertEquals([$this->searchCriteriaMock, null, null], $result);
    }

    /**
     * Test that HTTP context is not set when customer group ID is null
     */
    public function testBeforeGetListDoesNotSetHttpContextWhenCustomerGroupIsNull()
    {
        $customerId = 777;
        $exception = new LocalizedException(__('Error getting group'));

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

        // Should not set HTTP context when customer group ID is null
        $this->httpContextMock->expects($this->never())->method('setValue');

        $result = $this->plugin->beforeGetList($this->subjectMock, $this->searchCriteriaMock);

        $this->assertEquals([$this->searchCriteriaMock, null, null], $result);
    }
}
