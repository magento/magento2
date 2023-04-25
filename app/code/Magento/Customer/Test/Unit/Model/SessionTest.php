<?php declare(strict_types=1);
/**
 * Unit test for session \Magento\Customer\Model\Session
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Session\Storage;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Url;
use Magento\Framework\UrlFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends TestCase
{
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
     * @var Session
     */
    protected $_model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_storageMock = $this->getMockBuilder(Storage::class)
            ->addMethods(['getIsCustomerEmulated', 'unsIsCustomerEmulated'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->_httpContextMock = $this->createMock(Context::class);
        $this->urlFactoryMock = $this->createMock(UrlFactory::class);
        $this->customerFactoryMock = $this->getMockBuilder(CustomerFactory::class)->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->addMethods(['save'])
            ->getMock();
        $this->_customerResourceMock = $this->getMockBuilder(ResourceCustomer::class)->disableOriginalConstructor()
            ->onlyMethods(['load', 'save'])
            ->getMock();
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $helper = new ObjectManagerHelper($this);
        $this->responseMock = $this->createMock(Http::class);
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
                '_customerResource' => $this->_customerResourceMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testSetCustomerAsLoggedIn(): void
    {
        $customer = $this->createMock(Customer::class);
        $customerDto = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->expects($this->any())
            ->method('getDataModel')
            ->willReturn($customerDto);

        $this->_eventManagerMock
            ->method('dispatch')
            ->withConsecutive(
                ['customer_login', ['customer' => $customer]],
                ['customer_data_object_login', ['customer' => $customerDto]]
            );

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
        $customerDto = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customer);
        $customer->expects($this->once())
            ->method('updateData')
            ->with($customerDto)
            ->willReturnSelf();

        $this->_eventManagerMock
            ->method('dispatch')
            ->withConsecutive(
                ['customer_login', ['customer' => $customer]],
                ['customer_data_object_login', ['customer' => $customerDto]]
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
        $customerDataMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $customerDataMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $customerMock = $this->getMockBuilder(Customer::class)
            ->addMethods(['getConfirmation'])
            ->onlyMethods(['getId', 'updateData', 'getGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
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
     * @param bool $expectedResult
     * @param bool $isCustomerIdValid
     * @param bool $isCustomerEmulated
     *
     * @return void
     * @dataProvider getIsLoggedInDataProvider
     */
    public function testIsLoggedIn(
        bool $expectedResult,
        bool $isCustomerIdValid,
        bool $isCustomerEmulated
    ): void {
        $customerId = 1;
        $this->_storageMock->expects($this->any())->method('getData')->with('customer_id')
            ->willReturn($customerId);

        if ($isCustomerIdValid) {
            $this->customerRepositoryMock->expects($this->once())
                ->method('getById')
                ->with($customerId);
        } else {
            $this->customerRepositoryMock->expects($this->once())
                ->method('getById')
                ->with($customerId)
                ->willThrowException(new \Exception('Customer ID is invalid.'));
        }
        $this->_storageMock->expects($this->any())->method('getIsCustomerEmulated')
            ->willReturn($isCustomerEmulated);
        $this->assertEquals($expectedResult, $this->_model->isLoggedIn());
    }

    /**
     * @return array
     */
    public function getIsLoggedInDataProvider(): array
    {
        return [
            ['expectedResult' => true, 'isCustomerIdValid' => true, 'isCustomerEmulated' => false],
            ['expectedResult' => false, 'isCustomerIdValid' => true, 'isCustomerEmulated' => true],
            ['expectedResult' => false, 'isCustomerIdValid' => false, 'isCustomerEmulated' => false],
            ['expectedResult' => false, 'isCustomerIdValid' => false, 'isCustomerEmulated' => true]
        ];
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
        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($customerMock);

        $this->_storageMock
            ->expects($this->exactly(4))
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
}
