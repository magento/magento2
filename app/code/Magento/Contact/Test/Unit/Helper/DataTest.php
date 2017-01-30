<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    protected $_helper;

    /**
     * Scope config mock
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * Customer session mock
     *
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerSession;

    /**
     * Customer view helper mock
     *
     * @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerViewHelper;

    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = '\Magento\Contact\Helper\Data';
        $arguments = $objectManagerHelper->getConstructArguments($className);
        /**
         * @var \Magento\Framework\App\Helper\Context $context
         */
        $context = $arguments['context'];
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_customerSession = $arguments['customerSession'];
        $this->_customerViewHelper = $arguments['customerViewHelper'];
        $this->_helper = $objectManagerHelper->getObject($className, $arguments);
    }

    public function testIsEnabled()
    {
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue('1'));

        $this->assertTrue(is_string($this->_helper->isEnabled()));
    }

    public function testIsNotEnabled()
    {
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(null));

        $this->assertTrue(null === $this->_helper->isEnabled());
    }

    public function testGetUserNameNotLoggedIn()
    {
        $this->_customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->assertEmpty($this->_helper->getUserName());
    }

    public function testGetUserName()
    {
        $this->_customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $customerDataObject = $this->getMockBuilder('\Magento\Customer\Model\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_customerSession->expects($this->once())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customerDataObject));

        $this->_customerViewHelper->expects($this->once())
            ->method('getCustomerName')
            ->will($this->returnValue(' customer name '));

        $this->assertEquals('customer name', $this->_helper->getUserName());
    }

    public function testGetUserEmailNotLoggedIn()
    {
        $this->_customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->assertEmpty($this->_helper->getUserEmail());
    }

    public function testGetUserEmail()
    {
        $this->_customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        $customerDataObject = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        $customerDataObject->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue('customer@email.com'));

        $this->_customerSession->expects($this->once())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($customerDataObject));

        $this->assertEquals('customer@email.com', $this->_helper->getUserEmail());
    }
}
