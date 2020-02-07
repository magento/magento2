<?php
/**
 * Unit test for session \Magento\Customer\Model\Session
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_httpContextMock;

    /**
     * @var \Magento\Framework\UrlFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlFactoryMock;

    /**
     * @var \Magento\Customer\Model\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_model;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->_storageMock = $this->createPartialMock(
            \Magento\Customer\Model\Session\Storage::class,
            ['getIsCustomerEmulated', 'getData', 'unsIsCustomerEmulated', '__sleep', '__wakeup']
        );
        $this->_eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->_httpContextMock = $this->createMock(\Magento\Framework\App\Http\Context::class);
        $this->urlFactoryMock = $this->createMock(\Magento\Framework\UrlFactory::class);
        $this->customerFactoryMock = $this->getMockBuilder(\Magento\Customer\Model\CustomerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'save'])
            ->getMock();
        $this->customerRepositoryMock = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->_model = $helper->getObject(
            \Magento\Customer\Model\Session::class,
            [
                'customerFactory' => $this->customerFactoryMock,
                'storage' => $this->_storageMock,
                'eventManager' => $this->_eventManagerMock,
                'httpContext' => $this->_httpContextMock,
                'urlFactory' => $this->urlFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'response' => $this->responseMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testSetCustomerAsLoggedIn()
    {
        $customer = $this->createMock(\Magento\Customer\Model\Customer::class);
        $customerDto = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
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
        $customer = $this->createMock(\Magento\Customer\Model\Customer::class);
        $customerDto = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);

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
        $customerDataMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customerDataMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($customerId));

        $customerMock = $this->createPartialMock(
            \Magento\Customer\Model\Customer::class,
            ['getId', 'getConfirmation', 'updateData', 'getGroupId']
        );
        $customerMock->expects($this->exactly(3))
            ->method('getId')
            ->will($this->returnValue($customerId));
        $customerMock->expects($this->once())
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
        $customerMock = $this->createMock(\Magento\Customer\Model\Customer::class);
        $this->_storageMock->expects($this->once())->method('unsIsCustomerEmulated');
        $this->_model->setCustomer($customerMock);
    }
}
