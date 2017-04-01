<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Factory;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Interception\ObjectManager\Config\Compiled as CompiledConfig;
use Magento\Framework\ObjectManager\TestAsset\Basic;
use Magento\Framework\ObjectManager\TestAsset\DependsOnAlias;
use Magento\Framework\ObjectManager\TestAsset\HasOptionalParameters;
use Magento\Framework\ObjectManager\TestAsset\InterfaceImplementation;
use Magento\Framework\ObjectManager\TestAsset\TestAssetInterface;

class CompiledTest extends AbstractFactoryRuntimeDefinitionsTestCases
{
    /**
     * Child test cases should create this object using the type of factory they are testing
     *
     * @return AbstractFactory
     */
    protected function createFactoryToTest()
    {
        $diConfig = [
            'arguments' => [
                'Alias' => [
                    'requiredInterfaceParameter' => ['_i_' => InterfaceImplementation::class],
                    'requiredObjectParameter' => ['_i_' =>  Basic::class],
                    'optionalInterfaceParameter' => ['_i_' => InterfaceImplementation::class],
                    'optionalObjectParameter' => ['_i_' => Basic::class],
                    'optionalStringParameter' => ['_v_' => self::ALIAS_OVERRIDDEN_STRING],
                    'optionalIntegerParameter' => ['_v_' => self::ALIAS_OVERRIDDEN_INT],
                ],
                DependsOnAlias::class => [
                    'object' => ['_i_' => 'Alias']
                ],
            ],
            'instanceTypes' => [
                'Alias' => HasOptionalParameters::class
            ],
            'preferences' => [
                TestAssetInterface::class => InterfaceImplementation::class
            ]
        ];

        $compiledConfig = new CompiledConfig($diConfig);
        $factory = new Compiled($compiledConfig);
        $factory->setObjectManager(new ObjectManager($factory, $compiledConfig));
        return $factory;
    }
}
