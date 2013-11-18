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
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Core\Model\Layout\Argument\HandlerFactory
 */
namespace Magento\Core\Model\Layout\Argument;

class HandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\Argument\HandlerFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_model = new \Magento\Core\Model\Layout\Argument\HandlerFactory($this->_objectManagerMock);
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_objectManagerMock);
    }

    /**
     * @param $type
     * @expectedException \InvalidArgumentException
     * @dataProvider getArgumentHandlerFactoryByTypeWithNonStringTypeDataProvider
     */
    public function testGetArgumentHandlerByTypeWithNonStringType($type)
    {
        $this->_model->getArgumentHandlerByType($type);
    }

    public function getArgumentHandlerFactoryByTypeWithNonStringTypeDataProvider()
    {
        return array(
            'int value' => array(10),
            'object value' => array(new \StdClass()),
            'null value' => array(null),
            'boolean value' => array(false),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentHandlerFactoryByTypeWithInvalidType()
    {
        $this->_model->getArgumentHandlerByType('dummy_type');
    }

    /**
     * @param string $type
     * @param string $className
     * @dataProvider getArgumentHandlerFactoryByTypeWithValidTypeDataProvider
     */
    public function testGetArgumentHandlerFactoryByTypeWithValidType($type, $className)
    {
        $factoryMock = $this->getMock($className, array(), array(), '', false);
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className)
            ->will($this->returnValue($factoryMock));

        $this->assertInstanceOf($className, $this->_model->getArgumentHandlerByType($type));
    }

    public function getArgumentHandlerFactoryByTypeWithValidTypeDataProvider()
    {
        return array(
            'object'  => array('object', 'Magento\Core\Model\Layout\Argument\Handler\Object'),
            'options' => array('options', 'Magento\Core\Model\Layout\Argument\Handler\Options'),
            'url'     => array('url', 'Magento\Core\Model\Layout\Argument\Handler\Url')
        );
    }
}
