<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Arguments;

use \Magento\Framework\App\Arguments\ArgumentInterpreter;

class ArgumentInterpreterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Arguments\ArgumentInterpreter
     */
    private $object;

    protected function setUp()
    {
        $const = $this->createPartialMock(\Magento\Framework\Data\Argument\Interpreter\Constant::class, ['evaluate']);
        $const->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            ['value' => 'FIXTURE_INIT_PARAMETER']
        )->will(
            $this->returnValue('init_param_value')
        );
        $this->object = new ArgumentInterpreter($const);
    }

    public function testEvaluate()
    {
        $expected = ['argument' => 'init_param_value'];
        $this->assertEquals($expected, $this->object->evaluate(['value' => 'FIXTURE_INIT_PARAMETER']));
    }
}
