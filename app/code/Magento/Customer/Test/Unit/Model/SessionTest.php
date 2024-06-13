<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Session\Storage;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionStartChecker;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Url;
use Magento\Framework\UrlFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ResourceCustomer|MockObject
     */
    protected $_customerResourceMock;

    /**
     * @var Storage|MockObject
     */
    protected $_storageMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var Context|MockObject
     */
    protected $_httpContextMock;

    /**
     * @var UrlFactory|MockObject
     */
    protected $urlFactoryMock;

    /**
     * @var CustomerFactory|MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var Http|MockObject
     */
    protected $responseMock;

    /**
     * @var CustomerRegistry|MockObject
     */
    private $customerRegistryMock;

    /**
     * @var Session
     */
    protected $_model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_storageMock = $this->createPartialMockWithReflection(
            Storage::class,
            [
                'getIsCustomerEmulated',
                'unsIsCustomerEmulated',
                'getData'
            ]
        );
        $this->_eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->_httpContextMock = $this->createMock(Context::class);
        $this->urlFactoryMock = $this->createMock(UrlFactory::class);
        $this->customerFactoryMock = $this->createPartialMockWithReflection(
            CustomerFactory::class,
            ['create', 'save']
        );
        $this->_customerResourceMock = $this->getMockBuilder(ResourceCustomer::class)->disableOriginalConstructor()
            ->onlyMethods(['load', 'save'])
            ->getMock();
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $helper = new ObjectManagerHelper($this);
        $objects = [
            [
                SessionStartChecker::class,
                $this->createMock(SessionStartChecker::class)
            ]
        ];
        $helper->prepareObjectManager($objects);
        $this->responseMock = $this->createMock(Http::class);
        $this->customerRegistryMock = $this->createMock(CustomerRegistry::class);
        $this->_model = $helper->getObject(
            Session::class,
            [
                'customerFactory' => $this->customerFactoryMock,
                'storage' => $this->_storageMock,
                'eventManager' => $this->_eventManagerMock,
                'httpContext' => $this->_httpContextMock,
                'urlFactory' => $this->urlFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'response' => $this->responseMock,
                '_customerResource' => $this->_customerResourceMock,
                'customerRegistry' => $this->customerRegistryMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testSetCustomerAsLoggedIn(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerDto = $this->createMock(CustomerInterface::class);
        $customer->expects($this->any())
            ->method('getGroupId')
            ->willReturn(1);
        $customer->expects($this->any())
            ->method('getDataModel')
            ->willReturn($customerDto);

        $this->_eventManagerMock
            ->method('dispatch')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($customer, $customerDto) {
                    if ($arg1 == 'customer_login' && $arg2 == ['customer' => $customer]) {
                        return null;
                    } elseif ($arg1 == 'customer_data_object_login' && $arg2 == ['customer' => $customerDto]) {
                        return null;
                    }
                }
            );

        $this->_httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(CustomerContext::CONTEXT_GROUP, self::callback(fn($value): bool => $value === '1'), 0);

        $_SESSION = [];
        $this->_model->setCustomerAsLoggedIn($customer);
        $this->assertSame($customer, $this->_model->getCustomer());
    }

    /**
     * @return void
     */
    public function testSetCustomerDataAsLoggedIn(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerDto = $this->createMock(CustomerInterface::class);

        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customer);
        $customer->expects($this->once())
            ->method('updateData')
            ->with($customerDto)
            ->willReturnSelf();

        $this->_eventManagerMock
            ->method('dispatch')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($customer, $customerDto) {
                    if ($arg1 == 'customer_login' && $arg2 == ['customer' => $customer]) {
                        return null;
                    } elseif ($arg1 == 'customer_data_object_login' && $arg2 == ['customer' => $customerDto]) {
                        return null;
                    }
                }
            );

        $this->_model->setCustomerDataAsLoggedIn($customerDto);
        $this->assertSame($customer, $this->_model->getCustomer());
    }

    /**
     * @return void
     */
    public function testAuthenticate(): void
    {
        $urlMock = $this->createMock(Url::class);
        $urlMock->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturn('');
        $urlMock->expects($this->once())
            ->method('getRebuiltUrl')
            ->willReturn('');
        $this->urlFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturn($urlMock);
        $urlMock->expects($this->never())
            ->method('getUseSession')
            ->willReturn(false);

        $this->responseMock->expects($this->once())
            ->method('setRedirect')
            ->with('')
            ->willReturn('');

        $this->assertFalse($this->_model->authenticate());
    }

    /**
     * @return void
     */
    public function testLoginById(): void
    {
        $customerId = 1;

        $customerDataMock = $this->prepareLoginDataMock($customerId);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerDataMock);

        $this->assertTrue($this->_model->loginById($customerId));
    }

    /**
     * @param int $customerId
     *
     * @return MockObject
     */
    protected function prepareLoginDataMock(int $customerId): MockObject
    {
        $customerDataMock = $this->createMock(CustomerInterface::class);
        $customerDataMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $customerMock = $this->createPartialMockWithReflection(
            Customer::class,
            [
                'getConfirmation',
                'getId',
                'updateData',
                'getGroupId'
            ]
        );
        $customerMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->once())
            ->method('getConfirmation')
            ->willReturn($customerId);

        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);
        $customerMock->expects($this->once())
            ->method('updateData')
            ->with($customerDataMock)
            ->willReturnSelf();

        $this->_httpContextMock->expects($this->exactly(3))
            ->method('setValue');
        return $customerDataMock;
    }

    /**
     * @return void
     */
    public function testSetCustomerRemovesFlagThatShowsIfCustomerIsEmulated(): void
    {
        $customerMock = $this->createMock(Customer::class);
        $this->_storageMock->expects($this->once())->method('unsIsCustomerEmulated');
        $this->_model->setCustomer($customerMock);
    }

    /**
     * Test "getCustomer()" for guest user
     *
     * @return void
     */
    public function testGetCustomerForGuestUser(): void
    {
        $customerMock = $this->createMock(Customer::class);

        $this->customerFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $this->assertSame($customerMock, $this->_model->getCustomer());
    }

    /**
     * Test "getCustomer()" for registered user
     *
     * @return void
     */
    public function testGetCustomerForRegisteredUser(): void
    {
        $customerId = 1;

        $customerMock = $this->createMock(Customer::class);

        $this->customerFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $this->_storageMock
            ->expects($this->exactly(2))
            ->method('getData')
            ->with('customer_id')
            ->willReturn($customerId);

        $this->_customerResourceMock
            ->expects($this->once())
            ->method('load')
            ->with($customerMock, $customerId)
            ->willReturn($customerMock);

        $this->assertSame($customerMock, $this->_model->getCustomer());
    }

    public function testSetCustomer(): void
    {
        $customer = $this->createMock(Customer::class);
        $customer->expects($this->any())
            ->method('getGroupId')
            ->willReturn(1);
        $this->_httpContextMock->expects($this->once())
            ->method('setValue')
            ->with(CustomerContext::CONTEXT_GROUP, self::callback(fn($value): bool => $value === '1'), 0);

        $this->_model->setCustomer($customer);
    }

    public function testCheckCustomerId(): void
    {
        $customerId = 123;
        $customer = $this->createMock(Customer::class);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customer);
        $result = $this->_model->checkCustomerId($customerId);
        $this->assertTrue($result);
    }

    public function testCheckCustomerIdInvalid(): void
    {
        $customerId = 123;
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willThrowException(new NoSuchEntityException());
        $result = $this->_model->checkCustomerId($customerId);
        $this->assertFalse($result);
    }
}
