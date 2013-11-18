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
 * Test class for \Magento\Core\Model\Layout\Argument\Handler\Object
 */
namespace Magento\Core\Model\Layout\Argument\Handler;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\Argument\Handler\Object
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . 'TestObject.php');

        $helperObjectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_model = $helperObjectManager->getObject(
            'Magento\Core\Model\Layout\Argument\Handler\Object',
            array('objectManager' => $this->_objectManagerMock)
        );
    }

    /**
     * @dataProvider parseDataProvider()
     * @param \Magento\View\Layout\Element $argument
     * @param array $expectedResult
     */
    public function testParse($argument, $expectedResult)
    {
        $result = $this->_model->parse($argument);
        if (isset($result['updaters'])) {
            $result['updaters'] = array_values($result['updaters']);
        }
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function parseDataProvider()
    {
        $layout = simplexml_load_file(
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'arguments.xml',
            'Magento\View\Layout\Element'
        );
        $simpleObject = $layout->xpath('//argument[@name="testSimpleObject"]');
        $complexObject = $layout->xpath('//argument[@name="testComplexObject"]');
        return array(
            array(
                reset($simpleObject), array(
                    'value' => array(
                        'object' => 'Magento\Core\Model\Layout\Argument\Handler\TestObject',
                    ),
                    'type' => 'object',
                )
            ),
            array(
                reset($complexObject), array(
                    'value' => array(
                        'object' => 'Magento\Core\Model\Layout\Argument\Handler\TestObject',
                    ),
                    'type' => 'object',
                    'updaters' => array('Magento_Test_Updater')
                )
            ),
        );
    }

    /**
     * @dataProvider processDataProvider
     * @param array $argument
     */
    public function testProcess($argument)
    {
        $objectMock = $this->getMock(
            'Magento\Core\Model\Layout\Argument\Handler\TestObject', array(), array(), '', false, false
        );
        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Magento\Core\Model\Layout\Argument\Handler\TestObject')
            ->will($this->returnValue($objectMock));

        $this->assertSame($this->_model->process($argument), $objectMock);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return array(
            array(
                array(
                    'value' => array(
                        'object' => 'Magento\Core\Model\Layout\Argument\Handler\TestObject',
                    ),
                    'type' => 'object',
                )
            ),
        );
    }

    /**
     * @dataProvider processExceptionDataProvider
     * @param array $argument
     * @param string $message
     */
    public function testProcessException($argument, $message)
    {
        $this->setExpectedException(
            'InvalidArgumentException', $message
        );
        $this->_model->process($argument);
    }

    /**
     * @return array
     */
    public function processExceptionDataProvider()
    {

        return array(
            array(array('value' => null), 'Value is required for argument'),
            array(array('value' => array()), 'Passed value has incorrect format'),
            array(array('value' => array('object' => 'Test_Model')), 'Incorrect data source model'),
        );
    }
}
