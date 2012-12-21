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
 * @category    Mage
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class Mage_Core_Controller_Varien_Action_Factory
 */
class Mage_Core_Controller_Varien_Action_FactoryTest extends PHPUnit_Framework_TestCase
{
    /*
    * Test controller class name
    */
    const CONTROLLER_NAME  = 'TestController';

    /**
     * ObjectManager mock for tests
     *
     * @var Magento_ObjectManager_Zend
     */
    protected $_objectManager;

    /**
     * Test class instance
     *
     * @var Mage_Core_Controller_Varien_Action_Factory
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento_ObjectManager_Zend', array('create'), array(), '', false);
    }

    public function testConstruct()
    {
        $this->_model = new Mage_Core_Controller_Varien_Action_Factory($this->_objectManager);
        $this->assertAttributeInstanceOf('Magento_ObjectManager', '_objectManager', $this->_model);
    }

    public function testCreateController()
    {
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with(self::CONTROLLER_NAME, array())
            ->will($this->returnValue('TestControllerInstance'));

        $this->_model = new Mage_Core_Controller_Varien_Action_Factory($this->_objectManager);
        $this->assertEquals('TestControllerInstance', $this->_model->createController(self::CONTROLLER_NAME));
    }
}
