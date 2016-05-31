<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Helper
     *
     * @var \Magento\Contact\Helper\Data
     */
    protected $helper;

    /**
     * Scope config mock
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * Customer session mock
     *
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * Customer view helper mock
     *
     * @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerViewHelperMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = '\Magento\Contact\Helper\Data';
        $arguments = $this->objectManagerHelper->getConstructArguments($className);
        /**
         * @var \Magento\Framework\App\Helper\Context $context
         */
        $context = $arguments['context'];
        $this->scopeConfigMock = $context->getScopeConfig();
        $this->customerSessionMock = $arguments['customerSession'];
        $this->customerViewHelperMock = $arguments['customerViewHelper'];
        $this->helper = $this->objectManagerHelper->getObject($className, $arguments);
    }

    public function testIsEnabled()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn('1');

        $this->assertTrue(is_string($this->helper->isEnabled()));
    }

    public function testIsNotEnabled()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(null);

        $this->assertTrue(null === $this->helper->isEnabled());
    }

    public function testGetUserNameNotLoggedIn()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->assertEmpty($this->helper->getUserName());
    }

    public function testGetUserName()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $customerDataObject = $this->getMockBuilder('\Magento\Customer\Model\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerDataObject')
            ->willReturn($customerDataObject);

        $this->customerViewHelperMock->expects($this->once())
            ->method('getCustomerName')
            ->willReturn(' customer name ');

        $this->assertEquals('customer name', $this->helper->getUserName());
    }

    public function testGetUserEmailNotLoggedIn()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->assertEmpty($this->helper->getUserEmail());
    }

    public function testGetUserEmail()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $customerDataObject = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        $customerDataObject->expects($this->once())
            ->method('getEmail')
            ->willReturn('customer@email.com');

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerDataObject')
            ->willReturn($customerDataObject);

        $this->assertEquals('customer@email.com', $this->helper->getUserEmail());
    }

    public function testGetPostValue()
    {
        $postData = ['name' => 'Some Name', 'email' => 'Some Email'];

        $dataPersistorMock = $this->getMockBuilder('Magento\Framework\App\Request\DataPersistorInterface')
            ->getMockForAbstractClass();
        $dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('contact_us')
            ->willReturn($postData);
        $dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('contact_us');

        $this->objectManagerHelper->setBackwardCompatibleProperty($this->helper, 'dataPersistor', $dataPersistorMock);

        $this->assertSame($postData['name'], $this->helper->getPostValue('name'));
        $this->assertSame($postData['email'], $this->helper->getPostValue('email'));
    }
}
