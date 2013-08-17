<?php
/**
 * Magento_Outbound_Authentication_Factory
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Outbound_Authentication_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_mockObjectManager;

    /**
     * @var Magento_Outbound_Authentication_Factory
     */
    protected $_authFactory;

    /**
     * @var Magento_Outbound_Authentication_Hmac
     */
    protected $_expectedObject;

    public function setUp()
    {
        $this->_mockObjectManager = $this->getMockBuilder('Magento_ObjectManager')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_authFactory = new Magento_Outbound_Authentication_Factory(array('hmac' => 'Test_Authentication_Hmac'),
            $this->_mockObjectManager);

        $this->_expectedObject = $this->getMockBuilder('Magento_Outbound_Authentication_Hmac')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_formatterFactory = new Magento_Outbound_Formatter_Factory(
            array('json' => 'Test_Formatter_Json'),
            $this->_mockObjectManager
        );
    }

    public function testGetAuthenticationSuccess()
    {
        $this->_mockObjectManager->expects($this->once())
            ->method('get')
            ->with('Test_Authentication_Hmac')
            ->will($this->returnValue($this->_expectedObject));

        $authObject = $this->_authFactory->getAuthentication(Magento_Outbound_EndpointInterface::AUTH_TYPE_HMAC);
        $this->assertInstanceOf('Magento_Outbound_Authentication_Hmac', $authObject);
        $this->assertEquals($this->_expectedObject, $authObject);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage There is no authentication for the type given: TEST_AUTH_TYPE_STRING
     */
    public function testGetAuthenticationNoType()
    {
        $this->_authFactory->getAuthentication('TEST_AUTH_TYPE_STRING');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Authentication class for hmac does not implement authentication interface
     */
    public function testGetAuthenticationNoModel()
    {
        $this->_mockObjectManager->expects($this->once())
            ->method('get')
            ->with('Test_Authentication_Hmac')
            ->will($this->returnValue($this->getMock('Varien_Object')));
        $this->_authFactory->getAuthentication(Magento_Outbound_EndpointInterface::AUTH_TYPE_HMAC);
    }
}