<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\Chain\InterceptorSubstitution;

class InterceptorSubstitutionTest extends \PHPUnit_Framework_TestCase
{
    public function testModifyArgumentsDoNotExist()
    {
        $inputConfig = [
            'data' => []
        ];
        $modifier = new InterceptorSubstitution();
        $this->assertSame($inputConfig, $modifier->modify($inputConfig));
    }

    public function testModifyArguments()
    {
        $modifier = new InterceptorSubstitution();
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
                'Class' => [
                    'argument_type' => ['_i_' => 'Class\Dependency'],
                    'argument_not_shared' => ['_ins_' => 'Class\Dependency'],
                    'array_configured' => [
                        'argument_type' => ['_i_' => 'Class\Dependency'],
                        'argument_not_shared' => ['_ins_' => 'Class\Dependency'],
                        'array' => [
                            'argument_type' => ['_i_' => 'Class\Dependency'],
                            'argument_not_shared' => ['_ins_' => 'Class\DependencyIntercepted'],
                        ]
                    ]
                ],
                'virtualType' => [
                    'argument_type' => ['_i_' => 'Class\DependencyIntercepted'],
                    'argument_not_shared' => ['_ins_' => 'Class\Dependency'],
                    'array_configured' => ['banana']
                ],
                'Class\Interceptor' => [
                    'argument_type' => ['_i_' => 'Class\Dependency'],
                    'argument_not_shared' => ['_ins_' => 'Class\Dependency'],
                    'array_configured' => []
                ],

                'Class\DependencyIntercepted\Interceptor' => [],
                'Class\DependencyIntercepted' => []
            ],
            'preferences' => [
                'ClassInterface' => 'Class',
            ],
            'instanceTypes' => [
                'virtualType' => 'Class'
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
                'Class\Interceptor' => [
                    'argument_type' => ['_i_' => 'Class\Dependency'],
                    'argument_not_shared' => ['_ins_' => 'Class\Dependency'],
                    'array_configured' => [
                        'argument_type' => ['_i_' => 'Class\Dependency'],
                        'argument_not_shared' => ['_ins_' => 'Class\Dependency'],
                        'array' => [
                            'argument_type' => ['_i_' => 'Class\Dependency'],
                            'argument_not_shared' => ['_ins_' => 'Class\DependencyIntercepted'],
                        ]
                    ]
                ],
                'virtualType' => [
                    'argument_type' => ['_i_' => 'Class\DependencyIntercepted'],
                    'argument_not_shared' => ['_ins_' => 'Class\Dependency'],
                    'array_configured' => ['banana']
                ],
                'Class\DependencyIntercepted\Interceptor' => []
            ],
            'preferences' => [
                'ClassInterface' => 'Class\Interceptor',
                'Class' => 'Class\Interceptor',
                'Class\DependencyIntercepted' => 'Class\DependencyIntercepted\Interceptor'
            ],
            'instanceTypes' => [
                'virtualType' => 'Class\Interceptor',
            ]
        ];
    }
}
