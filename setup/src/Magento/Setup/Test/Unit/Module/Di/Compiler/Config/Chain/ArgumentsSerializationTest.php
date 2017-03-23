<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\Compiler\Config\Chain;

use \Magento\Setup\Module\Di\Compiler\Config\Chain\ArgumentsSerialization;

class ArgumentsSerializationTest extends \PHPUnit_Framework_TestCase
{
    public function testModifyArgumentsDoNotExist()
    {
        $inputConfig = [
            'data' => []
        ];
        $modifier = new ArgumentsSerialization();
        $this->assertSame($inputConfig, $modifier->modify($inputConfig));
    }

    public function testModifyArguments()
    {
        $inputConfig = [
            'arguments' => [
                'argument1' => [],
                'argument2' => null,
            ]
        ];

        $expected = [
            'arguments' => [
                'argument1' => serialize([]),
                'argument2' => null,
            ]
        ];

        $modifier = new ArgumentsSerialization();
        $this->assertEquals($expected, $modifier->modify($inputConfig));
    }
}
