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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App;

class AbstractShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\AbstractShell | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockBuilder(
            '\Magento\Framework\App\AbstractShell'
        )->disableOriginalConstructor()->setMethods(
            array('_applyPhpVariables')
        )->getMockForAbstractClass();
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @param array $arguments
     * @param string $argName
     * @param string $expectedValue
     *
     * @dataProvider setGetArgDataProvider
     */
    public function testSetGetArg($arguments, $argName, $expectedValue)
    {
        $this->_model->setRawArgs($arguments);
        $this->assertEquals($this->_model->getArg($argName), $expectedValue);
    }

    /**
     * @return array
     */
    public function setGetArgDataProvider()
    {
        return array(
            'argument with no value' => array(
                'arguments' => array('argument', 'argument2'),
                'argName' => 'argument',
                'expectedValue' => true
            ),
            'dashed argument with value' => array(
                'arguments' => array('-argument', 'value'),
                'argName' => 'argument',
                'expectedValue' => 'value'
            ),
            'double-dashed argument with separate value' => array(
                'arguments' => array('--argument-name', 'value'),
                'argName' => 'argument-name',
                'expectedValue' => 'value'
            ),
            'double-dashed argument with included value' => array(
                'arguments' => array('--argument-name=value'),
                'argName' => 'argument-name',
                'expectedValue' => 'value'
            ),
            'argument with value, then single argument with no value' => array(
                'arguments' => array('-argument', 'value', 'argument2'),
                'argName' => 'argument',
                'expectedValue' => 'value'
            )
        );
    }
}
