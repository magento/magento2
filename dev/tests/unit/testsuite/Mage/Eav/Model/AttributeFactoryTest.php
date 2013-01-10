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
 * @package     Mage_Eav
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Eav_Model_AttributeFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Eav_Model_AttributeFactory
     */
    protected $_factory;

    /**
     * @var array
     */
    protected $_arguments = array('test1', 'test2');

    /**
     * @var string
     */
    protected $_className = 'Test_Class';

    protected function setUp()
    {
        /** @var $objectManagerMock Magento_ObjectManager_Zend */
        $objectManagerMock = $this->getMock('Magento_ObjectManager_Zend', array(), array(), '', false);
        $objectManagerMock->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(array($this, 'getModelInstance')));

        $this->_factory = new Mage_Eav_Model_AttributeFactory($objectManagerMock);
    }

    protected function tearDown()
    {
        unset($this->_factory);
    }

    /**
     * @covers Mage_Eav_Model_AttributeFactory::createAttribute
     */
    public function testCreateAttribute()
    {
        $this->assertEquals($this->_className,
            $this->_factory->createAttribute($this->_className, $this->_arguments)
        );
    }

    public function getModelInstance($className, $arguments)
    {
        $this->assertInternalType('array', $arguments);
        $this->assertArrayHasKey('data', $arguments);
        $this->assertEquals($this->_arguments, $arguments['data']);

        return $className;
    }
}
