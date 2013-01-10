<?php
/**
 * Unit test for abstract service layer Mage_Core_Service_ServiceAbstract
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Service_ServiceAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Service_ServiceAbstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_service;

    /**
     * Initialize service abstract for testing
     */
    protected function setUp()
    {
        $this->_service = $this->getMockBuilder('Mage_Core_Service_ServiceAbstract')
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->_service);
    }

    /**
     * Test for _setDataUsingMethods method
     */
    public function testSetDataUsingMethods()
    {
        /** @var $entity Varien_Object|PHPUnit_Framework_MockObject_MockObject */
        $entity = $this->getMockBuilder('Varien_Object')
            ->setMethods(array('setPropertyA', 'setPropertyB'))
            ->getMock();

        $entity->expects($this->once())
            ->method('setPropertyA')
            ->with('a');

        $entity->expects($this->once())
            ->method('setPropertyB')
            ->with('b');

        $this->_callServiceProtectedMethod('_setDataUsingMethods',
            array($entity, array('property_a' => 'a', 'property_b' => 'b')));

        $this->assertEmpty($entity->getData());
    }

    /**
     * Call protected method of service
     *
     * @param string $method
     * @param array $arguments
     * @return
     */
    protected function _callServiceProtectedMethod($method, array $arguments = array())
    {
        $method = new ReflectionMethod($this->_service, $method);
        $method->setAccessible(true);
        return $method->invokeArgs($this->_service, $arguments);
    }
}
