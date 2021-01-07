<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Factory\Dynamic;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\Factory\AbstractFactory;
use Magento\Framework\ObjectManager\Factory\AbstractFactoryRuntimeDefinitionsTestCases;
use Magento\Framework\ObjectManager\TestAsset\Basic;
use Magento\Framework\ObjectManager\TestAsset\DependsOnAlias;
use Magento\Framework\ObjectManager\TestAsset\HasOptionalParameters;
use Magento\Framework\ObjectManager\TestAsset\InterfaceImplementation;
use Magento\Framework\ObjectManager\TestAsset\TestAssetInterface;

/**
 * @magentoAppIsolation enabled
 */
class DeveloperTest extends AbstractFactoryRuntimeDefinitionsTestCases
{
    /**
     * Child test cases should create this object using the type of factory they are testing
     *
     * @return AbstractFactory
     */
    protected function createFactoryToTest()
    {
        $runtimeDiConfig = [
            'preferences' => [
                TestAssetInterface::class => InterfaceImplementation::class
            ],
            'Alias' => [
                'type' => HasOptionalParameters::class,
                'arguments' => [
                    'requiredInterfaceParameter' => ['instance' => InterfaceImplementation::class],
                    'requiredObjectParameter' => ['instance' =>  Basic::class],
                    'optionalInterfaceParameter' => ['instance' => InterfaceImplementation::class],
                    'optionalObjectParameter' => ['instance' => Basic::class],
                    'optionalStringParameter' => self::ALIAS_OVERRIDDEN_STRING,
                    'optionalIntegerParameter' => self::ALIAS_OVERRIDDEN_INT,
                ]
            ],
            DependsOnAlias::class => [
                'arguments' => [
                    'object' => ['instance' => 'Alias']
                ]
            ]
        ];

        $runtimeConfig = new \Magento\Framework\Interception\ObjectManager\Config\Developer();
        $runtimeConfig->extend($runtimeDiConfig);
        $factory = new \Magento\Framework\ObjectManager\Factory\Dynamic\Developer($runtimeConfig);
        $factory->setObjectManager(new ObjectManager($factory, $runtimeConfig));
        return $factory;
    }
}
