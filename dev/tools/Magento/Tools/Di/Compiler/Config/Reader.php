<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\Di\Compiler\Config;

use Magento\Framework\App;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Tools\Di\Code\Reader\ClassReaderDecorator;
use Magento\Tools\Di\Code\Reader\Type;
use Magento\Tools\Di\Compiler\ArgumentsResolverFactory;
use Magento\Tools\Di\Definition\Collection as DefinitionsCollection;

class Reader
{
    /**
     * @var ConfigInterface
     */
    private $diContainerConfig;

    /**
     * @var App\ObjectManager\ConfigLoader
     */
    private $configLoader;

    /**
     * @var ArgumentsResolverFactory
     */
    private $argumentsResolverFactory;

    /**
     * @var ClassReaderDecorator
     */
    private $classReaderDecorator;

    /**
     * @var Type
     */
    private $typeReader;

    /**
     * @param ConfigInterface $diContainerConfig
     * @param App\ObjectManager\ConfigLoader $configLoader
     * @param ArgumentsResolverFactory $argumentsResolverFactory
     * @param ClassReaderDecorator $classReaderDecorator
     * @param Type $typeReader
     */
    public function __construct(
        \Magento\Framework\ObjectManager\ConfigInterface $diContainerConfig,
        App\ObjectManager\ConfigLoader $configLoader,
        ArgumentsResolverFactory $argumentsResolverFactory,
        ClassReaderDecorator $classReaderDecorator,
        Type $typeReader
    ) {
        $this->diContainerConfig = $diContainerConfig;
        $this->configLoader = $configLoader;
        $this->argumentsResolverFactory = $argumentsResolverFactory;
        $this->classReaderDecorator = $classReaderDecorator;
        $this->typeReader = $typeReader;
    }

    /**
     * Generates config per scope and returns it
     *
     * @param DefinitionsCollection $definitionsCollection
     * @param string $areaCode
     *
     * @return array
     */
    public function generateCachePerScope(
        DefinitionsCollection $definitionsCollection,
        $areaCode
    ) {
        $areaConfig = clone $this->diContainerConfig;
        if ($areaCode !== App\Area::AREA_GLOBAL) {
            $areaConfig->extend($this->configLoader->load($areaCode));
        }

        $config = [];
        $config['arguments'] = $this->getConfigForScope($definitionsCollection, $areaConfig);
        foreach ($config['arguments'] as $key => $value) {
            if ($value !== null) {
                $config['arguments'][$key] = serialize($value);
            }
        }
        foreach ($definitionsCollection->getInstancesNamesList() as $instanceName) {
            if (!$areaConfig->isShared($instanceName)) {
                $config['nonShared'][$instanceName] = true;
            }
            $preference = $areaConfig->getPreference($instanceName);
            if ($instanceName !== $preference) {
                $config['preferences'][$instanceName] = $preference;
            }
        }
        foreach (array_keys($areaConfig->getVirtualTypes()) as $virtualType) {
            $config['instanceTypes'][$virtualType] = $areaConfig->getInstanceType($virtualType);
        }
        return $config;
    }

    /**
     * Returns constructor with defined arguments
     *
     * @param DefinitionsCollection $definitionsCollection
     * @param ConfigInterface $config
     * @return array|mixed
     * @throws \ReflectionException
     */
    private function getConfigForScope(DefinitionsCollection $definitionsCollection, ConfigInterface $config)
    {
        $constructors = [];
        $argumentsResolver = $this->argumentsResolverFactory->create($config);
        foreach ($definitionsCollection->getInstancesNamesList() as $instanceType) {
            if (!$this->typeReader->isConcrete($instanceType)) {
                continue;
            }
            $constructor = $definitionsCollection->getInstanceArguments($instanceType);
            $constructors[$instanceType] = $argumentsResolver->getResolvedConstructorArguments(
                $instanceType,
                $constructor
            );
        }
        foreach (array_keys($config->getVirtualTypes()) as $instanceType) {
            $originalType = $config->getInstanceType($instanceType);
            if (!$definitionsCollection->hasInstance($originalType)) {
                if (!$this->typeReader->isConcrete($originalType)) {
                    continue;
                }
                $constructor = $this->classReaderDecorator->getConstructor($originalType);
            } else {
                $constructor = $definitionsCollection->getInstanceArguments($originalType);
            }
            $constructors[$instanceType] = $argumentsResolver->getResolvedConstructorArguments(
                $instanceType,
                $constructor
            );
        }
        return $constructors;
    }
}
