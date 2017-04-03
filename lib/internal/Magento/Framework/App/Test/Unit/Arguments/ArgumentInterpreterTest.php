<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Arguments;

use \Magento\Framework\App\Arguments\ArgumentInterpreter;

class ArgumentInterpreterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Arguments\ArgumentInterpreter
     */
    private $object;

    protected function setUp()
    {
        $const = $this->getMock(
            \Magento\Framework\Data\Argument\Interpreter\Constant::class,
            ['evaluate'],
            [],
            '',
            false
        );
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
