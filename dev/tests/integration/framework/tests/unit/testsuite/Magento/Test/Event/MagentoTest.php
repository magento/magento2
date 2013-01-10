<?php
/**
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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Magento_Test_Event_Magento.
 */
class Magento_Test_Event_MagentoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Event_Magento
     */
    protected $_object;

    /**
     * @var Magento_Test_EventManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManager;

    protected function setUp()
    {
        $this->_eventManager = $this->getMock('Magento_Test_EventManager', array('fireEvent'), array(array()));
        $this->_object = new Magento_Test_Event_Magento($this->_eventManager);
    }

    protected function tearDown()
    {
        Magento_Test_Event_Magento::setDefaultEventManager(null);
    }

    public function testConstructorDefaultEventManager()
    {
        Magento_Test_Event_Magento::setDefaultEventManager($this->_eventManager);
        $this->_object = new Magento_Test_Event_Magento();
        $this->testInitStoreAfter();
    }

    /**
     * @dataProvider constructorExceptionDataProvider
     * @expectedException Magento_Exception
     * @param mixed $eventManager
     */
    public function testConstructorException($eventManager)
    {
        new Magento_Test_Event_Magento($eventManager);
    }

    public function constructorExceptionDataProvider()
    {
        return array(
            'no event manager'     => array(null),
            'not an event manager' => array(new stdClass()),
        );
    }

    public function testInitStoreAfter()
    {
        $this->_eventManager
            ->expects($this->once())
            ->method('fireEvent')
            ->with('initStoreAfter')
        ;
        $this->_object->initStoreAfter();
    }
}
