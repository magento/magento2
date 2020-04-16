<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Fallback\Rule;

use \Magento\Framework\View\Design\Fallback\Rule\Composite;

/**
 * Composite Test
 *
 */
class CompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     */
    public function testConstructException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Each item should implement the fallback rule interface');

        new Composite([new \stdClass()]);
    }

    public function testGetPatternDirs()
    {
        $inputParams = ['param_one' => 'value_one', 'param_two' => 'value_two'];

        $ruleOne = $this->getMockForAbstractClass(\Magento\Framework\View\Design\Fallback\Rule\RuleInterface::class);
        $ruleOne->expects(
            $this->once()
        )->method(
            'getPatternDirs'
        )->with(
            $inputParams
        )->willReturn(
            ['rule_one/path/one', 'rule_one/path/two']
        );

        $ruleTwo = $this->getMockForAbstractClass(\Magento\Framework\View\Design\Fallback\Rule\RuleInterface::class);
        $ruleTwo->expects(
            $this->once()
        )->method(
            'getPatternDirs'
        )->with(
            $inputParams
        )->willReturn(
            ['rule_two/path/one', 'rule_two/path/two']
        );

        $object = new Composite([$ruleOne, $ruleTwo]);

        $expectedResult = ['rule_one/path/one', 'rule_one/path/two', 'rule_two/path/one', 'rule_two/path/two'];
        $this->assertEquals($expectedResult, $object->getPatternDirs($inputParams));
    }
}
