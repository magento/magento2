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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_Layout_Factory
 */
class Mage_Core_Model_Layout_FactoryTest extends PHPUnit_Framework_TestCase
{
    /*
     * Test class name
     */
    const CLASS_NAME  = 'Mage_Core_Model_Layout';

    /**
     * Test arguments
     *
     * @var array
     */
    protected $_arguments = array();

    /**
     * ObjectManager mock for tests
     *
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Test class instance
     *
     * @var Mage_Core_Model_Layout_Factory
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento_ObjectManager_Zend',
            array('hasSharedInstance', 'get', 'removeSharedInstance', 'create', 'addSharedInstance'),
            array(), '', false);
        $this->_model = new Mage_Core_Model_Layout_Factory($this->_objectManager);
    }

    public function testConstruct()
    {
        $this->assertAttributeInstanceOf('Magento_ObjectManager', '_objectManager', $this->_model);
    }

    public function testCreateLayoutRemoveSharedInstance()
    {
        $this->_arguments = array('area' => 'ArgumentArea');

        $this->_objectManager->expects($this->once())
            ->method('hasSharedInstance')
            ->with(self::CLASS_NAME)
            ->will($this->returnValue(true));

        $modelLayout = $this->getMock(self::CLASS_NAME, array('getArea'), array(), '', false);

        $this->_objectManager->expects($this->at(1))
            ->method('get')
            ->with(self::CLASS_NAME)
            ->will($this->returnValue($modelLayout));

        $modelLayout->expects($this->any())
            ->method('getArea')
            ->will($this->returnValue('TestArea'));

        $this->_objectManager->expects($this->once())
            ->method('removeSharedInstance')
            ->with(self::CLASS_NAME);

        $newModelLayout = $this->getMock(self::CLASS_NAME, array(), array(), '', false);

        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with(self::CLASS_NAME, $this->_arguments, false)
            ->will($this->returnValue($newModelLayout));

        $this->_objectManager->expects($this->once())
            ->method('addSharedInstance')
            ->with($newModelLayout, self::CLASS_NAME);

        $this->_objectManager->expects($this->at(5))
            ->method('get')
            ->with(self::CLASS_NAME)
            ->will($this->returnValue($newModelLayout));

        $this->assertEquals($newModelLayout, $this->_model->createLayout($this->_arguments));
    }

    public function testCreateLayoutNew()
    {
        $modelLayout = $this->getMock(self::CLASS_NAME, array(), array(), '', false);

        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with(self::CLASS_NAME, array(), false)
            ->will($this->returnValue($modelLayout));

        $this->_objectManager->expects($this->once())
            ->method('addSharedInstance')
            ->with($modelLayout, self::CLASS_NAME);

        $this->_objectManager->expects($this->at(2))
            ->method('get')
            ->with(self::CLASS_NAME)
            ->will($this->returnValue($modelLayout));

        $this->assertEquals($modelLayout, $this->_model->createLayout());
    }

    public function testCreateLayoutGetSharedInstance()
    {
        $this->_arguments = array('area' => 'TestArea');

        $this->_objectManager->expects($this->once())
            ->method('hasSharedInstance')
            ->with(self::CLASS_NAME)
            ->will($this->returnValue(true));

        $modelLayout = $this->getMock(self::CLASS_NAME, array('getArea'), array(), '', false);

        $this->_objectManager->expects($this->at(1))
            ->method('get')
            ->with(self::CLASS_NAME)
            ->will($this->returnValue($modelLayout));

        $modelLayout->expects($this->any())
            ->method('getArea')
            ->will($this->returnValue('TestArea'));

        $this->_objectManager->expects($this->never())
            ->method('removeSharedInstance');

        $this->_objectManager->expects($this->never())
            ->method('create');

        $this->_objectManager->expects($this->never())
            ->method('addSharedInstance');

        $this->_objectManager->expects($this->at(2))
            ->method('get')
            ->with(self::CLASS_NAME)
            ->will($this->returnValue($modelLayout));

        $this->assertEquals($modelLayout, $this->_model->createLayout($this->_arguments));
    }
}
