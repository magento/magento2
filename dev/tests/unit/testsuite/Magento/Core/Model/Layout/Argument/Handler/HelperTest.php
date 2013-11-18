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
 * Test class for \Magento\Core\Model\Layout\Argument\Handler\Helper
 */
namespace Magento\Core\Model\Layout\Argument\Handler;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\Argument\Handler\Helper
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        include_once(__DIR__ . DIRECTORY_SEPARATOR . 'TestHelper.php');

        $helperObjectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $this->_model = $helperObjectManager->getObject(
            'Magento\Core\Model\Layout\Argument\Handler\Helper',
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
        $resultWithParams = $resultWithoutParams = $result[0][0];
        $resultWithoutParams['value']['params'] = array();
        $argWithParams = $layout->xpath('//argument[@name="testHelperWithParams"]');
        $argWithoutParams = $layout->xpath('//argument[@name="testHelperWithoutParams"]');
        return array(
            array($argWithParams[0], $resultWithParams + array('type' => 'helper')),
            array($argWithoutParams[0], $resultWithoutParams + array('type' => 'helper')),
        );
    }

    /**
     * @dataProvider processDataProvider
     * @param array $argument
     * @param boolean $expectedResult
     */
    public function testProcess($argument, $expectedResult)
    {
        $helperMock = $this->getMock(
            'Magento\Core\Model\Layout\Argument\Handler\TestHelper', array(), array(), '', false, false
        );
        $helperMock->expects($this->once())
            ->method('testMethod')
            ->with('firstValue', 'secondValue')
            ->will($this->returnValue($expectedResult));
        $this->_objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Magento\Core\Model\Layout\Argument\Handler\TestHelper')
            ->will($this->returnValue($helperMock));

        $this->assertEquals($this->_model->process($argument), $expectedResult);
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
                        'helperClass' => 'Magento\Core\Model\Layout\Argument\Handler\TestHelper',
                        'helperMethod' => 'testMethod',
                        'params' => array(
                            'firstValue',
                            'secondValue',
                        ),
                    )
                )
                , true
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
        $argument = $this->processDataProvider();
        $invalidHelper = $invalidMethod = $nonExisting = $emptyValue = $argument[0][0];
        unset($invalidHelper['value']['helperClass']);
        unset($invalidMethod['value']['helperMethod']);
        $nonExisting['value']['helperClass'] = 'Dummy_Helper';
        $nonExisting['value']['helperMethod'] = 'dummyMethod';
        unset($emptyValue['value']);

        return array(
            array($invalidHelper, 'Passed helper has incorrect format'),
            array($invalidMethod, 'Passed helper has incorrect format'),
            array($nonExisting, 'Helper method "Dummy_Helper::dummyMethod" does not exist'),
            array($nonExisting, 'Helper method "Dummy_Helper::dummyMethod" does not exist'),
            array($emptyValue, 'Value is required for argument'),
        );
    }
}
