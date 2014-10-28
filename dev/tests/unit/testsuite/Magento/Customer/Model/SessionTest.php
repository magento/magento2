<?php
/**
 * Unit test for session \Magento\Customer\Model\Session
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converterMock;

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
     * @var \Magento\Customer\Service\V1\CustomerAccountService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountServiceMock;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_model;

    protected function setUp()
    {
        $this->_converterMock = $this->getMock('Magento\Customer\Model\Converter', [], [], '', false);
        $this->_storageMock = $this->getMock(
            'Magento\Customer\Model\Session\Storage',
            ['getIsCustomerEmulated', 'getData', 'unsIsCustomerEmulated', '__sleep', '__wakeup'],
            [],
            '',
            false
        );
        $this->_eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $this->_httpContextMock = $this->getMock('Magento\Framework\App\Http\Context', [], [], '', false);
        $this->urlFactoryMock = $this->getMock('Magento\Framework\UrlFactory', [], [], '', false);
        $this->customerAccountServiceMock = $this->getMock(
            'Magento\Customer\Service\V1\CustomerAccountService',
            [],
            [],
            '',
            false
        );
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Customer\Model\Session',
            [
                'converter' => $this->_converterMock,
                'storage' => $this->_storageMock,
                'eventManager' => $this->_eventManagerMock,
                'httpContext' => $this->_httpContextMock,
                'urlFactory' => $this->urlFactoryMock,
                'customerAccountService' => $this->customerAccountServiceMock,
            ]
        );
    }

    public function testSetCustomerAsLoggedIn()
    {
        $customer = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);
        $customerDto = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $this->_converterMock->expects($this->any())
            ->method('createCustomerFromModel')
            ->will($this->returnValue($customerDto));

        $this->_eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('customer_login', ['customer' => $customer]);
        $this->_eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('customer_data_object_login', ['customer' => $customerDto]);

        $_SESSION = array();
        $this->_model->setCustomerAsLoggedIn($customer);
        $this->assertSame($customer, $this->_model->getCustomer());
    }

    public function testSetCustomerDataAsLoggedIn()
    {
        $customer = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);
        $customerDto = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);

        $this->_converterMock->expects($this->any())
            ->method('createCustomerModel')
            ->will($this->returnValue($customer));

        $this->_eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('customer_login', ['customer' => $customer]);
        $this->_eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('customer_data_object_login', ['customer' => $customerDto]);

        $this->_model->setCustomerDataAsLoggedIn($customerDto);
        $this->assertSame($customer, $this->_model->getCustomer());
    }

    public function testAuthenticate()
    {
        $urlMock = $this->getMock('Magento\Framework\Url', array(), array(), '', false);
        $urlMock->expects($this->exactly(2))
            ->method('getUrl')
            ->will($this->returnValue(''));
        $urlMock->expects($this->once())
            ->method('getRebuiltUrl')
            ->will($this->returnValue(''));
        $this->urlFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->will($this->returnValue($urlMock));

        $responseMock = $this->getMock('Magento\Framework\App\Response\Http', array(), array(), '', false);
        $responseMock->expects($this->once())
            ->method('setRedirect')
            ->with('')
            ->will($this->returnValue(''));

        $actionMock = $this->getMock('Magento\Framework\App\Action\Action', array(), array(), '', false);
        $actionMock->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($responseMock));

        $this->assertFalse($this->_model->authenticate($actionMock));
    }

    public function testLoginById()
    {
        $customerId = 1;

        $customerDataMock = $this->prepareLoginDataMock($customerId);

        $this->customerAccountServiceMock->expects($this->once())
            ->method('getCustomer')
            ->with($this->equalTo($customerId))
            ->will($this->returnValue($customerDataMock));

        $this->assertTrue($this->_model->loginById($customerId));
    }

    /**
     * @param $customerId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareLoginDataMock($customerId)
    {
        $customerDataMock = $this->getMock('Magento\Customer\Service\V1\Data\Customer', array(), array(), '', false);
        $customerDataMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($customerId));

        $customerMock = $this->getMock('Magento\Customer\Model\Customer', array(), array(), '', false);
        $customerMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($customerId));
        $customerMock->expects($this->once())
            ->method('isConfirmationRequired')
            ->will($this->returnValue(true));
        $customerMock->expects($this->never())
            ->method('getConfirmation')
            ->will($this->returnValue($customerId));

        $this->_converterMock->expects($this->once())
            ->method('createCustomerModel')
            ->with($customerDataMock)
            ->will($this->returnValue($customerMock));

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
            $this->customerAccountServiceMock->expects($this->once())
                ->method('getCustomer')
                ->with($customerId);
        } else {
            $this->customerAccountServiceMock->expects($this->once())
                ->method('getCustomer')
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
        return array(
            array('expectedResult' => true, 'isCustomerIdValid' => true, 'isCustomerEmulated' => false,),
            array('expectedResult' => false, 'isCustomerIdValid' => true, 'isCustomerEmulated' => true,),
            array('expectedResult' => false, 'isCustomerIdValid' => false, 'isCustomerEmulated' => false,),
            array('expectedResult' => false, 'isCustomerIdValid' => false, 'isCustomerEmulated' => true,),
        );
    }

    public function testSetCustomerRemovesFlagThatShowsIfCustomerIsEmulated()
    {
        $customerMock = $this->getMock('Magento\Customer\Model\Customer', array(), array(), '', false);
        $this->_storageMock->expects($this->once())->method('unsIsCustomerEmulated');
        $this->_model->setCustomer($customerMock);
    }
}
