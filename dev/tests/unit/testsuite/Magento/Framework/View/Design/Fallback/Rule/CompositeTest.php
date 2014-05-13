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
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * Composite Test
 *
 */
class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Each item should implement the fallback rule interface
     */
    public function testConstructException()
    {
        new Composite(array(new \stdClass()));
    }

    public function testGetPatternDirs()
    {
        $inputParams = array('param_one' => 'value_one', 'param_two' => 'value_two');

        $ruleOne = $this->getMockForAbstractClass('\Magento\Framework\View\Design\Fallback\Rule\RuleInterface');
        $ruleOne->expects(
            $this->once()
        )->method(
            'getPatternDirs'
        )->with(
            $inputParams
        )->will(
            $this->returnValue(array('rule_one/path/one', 'rule_one/path/two'))
        );

        $ruleTwo = $this->getMockForAbstractClass('\Magento\Framework\View\Design\Fallback\Rule\RuleInterface');
        $ruleTwo->expects(
            $this->once()
        )->method(
            'getPatternDirs'
        )->with(
            $inputParams
        )->will(
            $this->returnValue(array('rule_two/path/one', 'rule_two/path/two'))
        );

        $object = new Composite(array($ruleOne, $ruleTwo));

        $expectedResult = array('rule_one/path/one', 'rule_one/path/two', 'rule_two/path/one', 'rule_two/path/two');
        $this->assertEquals($expectedResult, $object->getPatternDirs($inputParams));
    }
}
