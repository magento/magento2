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
namespace Magento\App\Arguments;

class ArgumentInterpreterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Arguments\ArgumentInterpreter
     */
    private $object;

    /**
     * @var \Magento\App\Arguments|\PHPUnit_Framework_MockObject_MockObject
     */
    private $arguments;

    protected function setUp()
    {
        $this->arguments = $this->getMock('\Magento\App\Arguments', array('get'), array(), '', false);
        $const = $this->getMock('\Magento\Data\Argument\Interpreter\Constant', array('evaluate'), array(), '', false);
        $const->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            array('value' => 'FIXTURE_INIT_PARAMETER')
        )->will(
            $this->returnValue('init_param_value')
        );
        $this->object = new ArgumentInterpreter($this->arguments, $const);
    }

    public function testEvaluate()
    {
        $expected = 'test_value';
        $this->arguments->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'init_param_value'
        )->will(
            $this->returnValue($expected)
        );
        $this->assertEquals($expected, $this->object->evaluate(array('value' => 'FIXTURE_INIT_PARAMETER')));
    }

    /**
     * @expectedException \Magento\Data\Argument\MissingOptionalValueException
     * @expectedExceptionMessage Value of application argument 'init_param_value' is not defined.
     */
    public function testEvaluateException()
    {
        $this->arguments->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'init_param_value'
        )->will(
            $this->returnValue(null)
        );
        $this->object->evaluate(array('value' => 'FIXTURE_INIT_PARAMETER'));
    }
}
