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
namespace Magento\Framework\Data\Argument\Interpreter;

class NumberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Number
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Number();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Numeric value is expected
     *
     * @dataProvider evaluateExceptionDataProvider
     */
    public function testEvaluateException($input)
    {
        $this->_model->evaluate($input);
    }

    public function evaluateExceptionDataProvider()
    {
        return array('no value' => array(array()), 'non-numeric value' => array(array('value' => 'non-numeric')));
    }

    /**
     * @param array $input
     * @param bool $expected
     *
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate($input, $expected)
    {
        $actual = $this->_model->evaluate(array('value' => $input));
        $this->assertSame($expected, $actual);
    }

    public function evaluateDataProvider()
    {
        return array(
            'integer' => array(10, 10),
            'float' => array(10.5, 10.5),
            'string numeric (integer)' => array('10', '10'),
            'string numeric (float)' => array('10.5', '10.5')
        );
    }
}
