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
 * Test class for \Magento\Core\Model\Layout\Argument\Handler\Boolean
 */
namespace Magento\Core\Model\Layout\Argument\Handler;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\Argument\Handler\Boolean
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $helperObjectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_model = $helperObjectManager->getObject(
            'Magento\Core\Model\Layout\Argument\Handler\Boolean',
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
        $result = $this->processDataProvider();
        $simpleArg = $layout->xpath('//argument[@name="testSimpleBoolean"]');
        $complexArg = $layout->xpath('//argument[@name="testComplexBoolean"]');
        return array(
            array($simpleArg[0], $result[0][0] + array('type' => 'boolean')),
            array($complexArg[0], $result[0][0] + array('type' => 'boolean')),
        );
    }

    /**
     * @dataProvider processDataProvider
     * @param array $argument
     * @param boolean $expectedResult
     */
    public function testProcess($argument, $expectedResult)
    {
        $this->assertEquals($this->_model->process($argument), $expectedResult);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return array(
            array(array('value' => 'true'), true),
            array(array('value' => 'false'), false),
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
            array(array('value' => 'wrong'), 'Value is not boolean argument'),
        );
    }
}
