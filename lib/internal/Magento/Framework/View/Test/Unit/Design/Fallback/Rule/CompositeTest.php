<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Design\Fallback\Rule;

use Magento\Framework\View\Design\Fallback\Rule\Composite;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    public function testConstructException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Each item should implement the fallback rule interface');
        new Composite([new \stdClass()]);
    }

    public function testGetPatternDirs()
    {
        $inputParams = ['param_one' => 'value_one', 'param_two' => 'value_two'];

        $ruleOne = $this->getMockForAbstractClass(RuleInterface::class);
        $ruleOne->expects(
            $this->once()
        )->method(
            'getPatternDirs'
        )->with(
            $inputParams
        )->willReturn(
            ['rule_one/path/one', 'rule_one/path/two']
        );

        $ruleTwo = $this->getMockForAbstractClass(RuleInterface::class);
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
