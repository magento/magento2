<?php
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
use Magento\Framework\UrlFactory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends \PHPUnit\Framework\TestCase
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
     * @return void
     */
    protected function setUp()
    {
        $this->_storageMock = $this->createPartialMock(
            Storage::class,
            ['getIsCustomerEmulated', 'getData', 'unsIsCustomerEmulated', '__sleep', '__wakeup']
        );
        $this->_eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->_httpContextMock = $this->createMock(Context::class);
        $this->urlFactoryMock = $this->createMock(UrlFactory::class);
        $this->customerFactoryMock = $this->getMockBuilder(CustomerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->_customerResourceMock = $this->getMockBuilder(ResourceCustomer::class)
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMock();
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
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
                '_customerResource' => $this->_customerResourceMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testSetCustomerAsLoggedIn()
    {
        $customer = $this->createMock(Customer::class);
        $customerDto = $this->createMock(CustomerInterface::class);
        $customer->expects($this->any())
            ->method('getDataModel')
            ->will($this->returnValue($customerDto));

        $this->_eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('customer_login', ['customer' => $customer]);
        $this->_eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('customer_data_object_login', ['customer' => $customerDto]);

        $_SESSION = [];
        $this->_model->setCustomerAsLoggedIn($customer);
        $this->assertSame($customer, $this->_model->getCustomer());
    }

    /**
     * @return void
     */
    public function testSetCustomerDataAsLoggedIn()
    {
        $customer = $this->createMock(Customer::class);
        $customerDto = $this->createMock(CustomerInterface::class);

        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($customer));
        $customer->expects($this->once())
            ->method('updateData')
            ->with($customerDto)
            ->will($this->returnSelf());

        $this->_eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('customer_login', ['customer' => $customer]);
        $this->_eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('customer_data_object_login', ['customer' => $customerDto]);

        $this->_model->setCustomerDataAsLoggedIn($customerDto);
        $this->assertSame($customer, $this->_model->getCustomer());
    }

    /**
     * @return void
     */
    public function testAuthenticate()
    {
        $urlMock = $this->createMock(\Magento\Framework\Url::class);
        $urlMock->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturn('');
        $urlMock->expects($this->once())
            ->method('getRebuiltUrl')
            ->willReturn('');
        $this->urlFactoryMock->expects($this->exactly(4))
            ->method('create')
            ->willReturn($urlMock);
        $urlMock->expects($this->once())
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
    public function testLoginById()
    {
        $customerId = 1;

        $customerDataMock = $this->prepareLoginDataMock($customerId);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($this->equalTo($customerId))
            ->will($this->returnValue($customerDataMock));

        $this->assertTrue($this->_model->loginById($customerId));
    }

    /**
     * @param int $customerId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareLoginDataMock($customerId)
    {
        $customerDataMock = $this->createMock(CustomerInterface::class);
        $customerDataMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($customerId));

        $customerMock = $this->createPartialMock(
            Customer::class,
            ['getId', 'isConfirmationRequired', 'getConfirmation', 'updateData', 'getGroupId']
        );
        $customerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($customerId));
        $customerMock->expects($this->once())
            ->method('isConfirmationRequired')
            ->will($this->returnValue(true));
        $customerMock->expects($this->never())
            ->method('getConfirmation')
            ->will($this->returnValue($customerId));

        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($customerMock));
        $customerMock->expects($this->once())
            ->method('updateData')
            ->with($customerDataMock)
            ->will($this->returnSelf());

        $this->_httpContextMock->expects($this->exactly(3))
            ->method('setValue');
        return $customerDataMock;
    }

    /**
     * @param bool $expectedResult
     * @param bool $isCustomerIdValid
     * @param bool $isCustomerEmulated
     * @dataProvider getIsLoggedInDataProvider
     */
    public function testIsLoggedIn($expectedResult, $isCustomerIdValid, $isCustomerEmulated)
    {
        $customerId = 1;
        $this->_storageMock->expects($this->any())->method('getData')->with('customer_id')
            ->will($this->returnValue($customerId));

        if ($isCustomerIdValid) {
            $this->customerRepositoryMock->expects($this->once())
                ->method('getById')
                ->with($customerId);
        } else {
            $this->customerRepositoryMock->expects($this->once())
                ->method('getById')
                ->with($customerId)
                ->will($this->throwException(new \Exception('Customer ID is invalid.')));
        }
        $this->_storageMock->expects($this->any())->method('getIsCustomerEmulated')
            ->will($this->returnValue($isCustomerEmulated));
        $this->assertEquals($expectedResult, $this->_model->isLoggedIn());
    }

    /**
     * @return array
     */
    public function getIsLoggedInDataProvider()
    {
        return [
            ['expectedResult' => true, 'isCustomerIdValid' => true, 'isCustomerEmulated' => false],
            ['expectedResult' => false, 'isCustomerIdValid' => true, 'isCustomerEmulated' => true],
            ['expectedResult' => false, 'isCustomerIdValid' => false, 'isCustomerEmulated' => false],
            ['expectedResult' => false, 'isCustomerIdValid' => false, 'isCustomerEmulated' => true],
        ];
    }

    /**
     * @return void
     */
    public function testSetCustomerRemovesFlagThatShowsIfCustomerIsEmulated()
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
    public function testGetCustomerForGuestUser()
    {
        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($customerMock));

        $this->assertSame($customerMock, $this->_model->getCustomer());
    }

    /**
     * Test "getCustomer()" for registered user
     *
     * @return void
     */
    public function testGetCustomerForRegisteredUser()
    {
        $customerId = 1;

        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($customerMock));

        $this->_storageMock
            ->expects($this->exactly(4))
            ->method('getData')
            ->with('customer_id')
            ->willReturn($customerId);

        $this->_customerResourceMock
            ->expects($this->once())
            ->method('load')
            ->with($customerMock, $customerId)
            ->will($this->returnValue($customerMock));

        $this->assertSame($customerMock, $this->_model->getCustomer());
    }
}
