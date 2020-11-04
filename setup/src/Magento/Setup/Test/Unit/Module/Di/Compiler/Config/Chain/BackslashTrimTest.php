<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\Chain\BackslashTrim;
use PHPUnit\Framework\TestCase;

class BackslashTrimTest extends TestCase
{
    public function testModifyArgumentsDoNotExist()
    {
        $inputConfig = [
            'data' => []
        ];
        $modifier = new BackslashTrim();
        $this->assertSame($inputConfig, $modifier->modify($inputConfig));
    }

    public function testModifyArguments()
    {
        $modifier = new BackslashTrim();
        $this->assertEquals($this->getOutputConfig(), $modifier->modify($this->getInputConfig()));
    }

    /**
     * Input config
     *
     * @return array
     */
    private function getInputConfig()
    {
        return [
            'arguments' => [
                '\\Class' => [
                    'argument_type' => ['_i_' => '\\Class\\Dependency'],
                    'argument_not_shared' => ['_ins_' => '\\Class\\Dependency'],
                    'array' => [
                        'argument_type' => ['_i_' => '\\Class\\Dependency'],
                        'argument_not_shared' => ['_ins_' => '\\Class\\Dependency'],
                        'array' => [
                            'argument_type' => ['_i_' => '\\Class\\Dependency'],
                            'argument_not_shared' => ['_ins_' => '\\Class\\Dependency'],
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Output config
     *
     * @return array
     */
    private function getOutputConfig()
    {
        return [
            'arguments' => [
                'Class' => [
                    'argument_type' => ['_i_' => 'Class\\Dependency'],
                    'argument_not_shared' => ['_ins_' => 'Class\\Dependency'],
                    'array' => [
                        'argument_type' => ['_i_' => 'Class\\Dependency'],
                        'argument_not_shared' => ['_ins_' => 'Class\\Dependency'],
                        'array' => [
                            'argument_type' => ['_i_' => 'Class\\Dependency'],
                            'argument_not_shared' => ['_ins_' => 'Class\\Dependency'],
                        ]
                    ]
                ]
            ]
        ];
    }
}
