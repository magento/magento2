<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config;

use Magento\Framework\App;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Setup\Module\Di\Code\Reader\Type;
use Magento\Setup\Module\Di\Compiler\ArgumentsResolverFactory;
use Magento\Setup\Module\Di\Definition\Collection as DefinitionsCollection;

/**
 * Class Reader
 * @package Magento\Setup\Module\Di\Compiler\Config
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Reader
{
    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    private $diContainerConfig;

    /**
     * @var App\ObjectManager\ConfigLoader
     * @since 2.0.0
     */
    private $configLoader;

    /**
     * @var ArgumentsResolverFactory
     * @since 2.0.0
     */
    private $argumentsResolverFactory;

    /**
     * @var ClassReaderDecorator
     * @since 2.0.0
     */
    private $classReaderDecorator;

    /**
     * @var Type
     * @since 2.0.0
     */
    private $typeReader;

    /**
     * @param ConfigInterface $diContainerConfig
     * @param App\ObjectManager\ConfigLoader $configLoader
     * @param ArgumentsResolverFactory $argumentsResolverFactory
     * @param ClassReaderDecorator $classReaderDecorator
     * @param Type $typeReader
     * @since 2.0.0
     */
    public function __construct(
        ConfigInterface $diContainerConfig,
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
     * @since 2.0.0
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
        
        $this->fillThirdPartyInterfaces($areaConfig, $definitionsCollection);
        $config['arguments'] = $this->getConfigForScope($definitionsCollection, $areaConfig);

        foreach ($definitionsCollection->getInstancesNamesList() as $instanceName) {
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
     * @since 2.0.0
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

    /**
     * Returns preferences for third party code
     *
     * @param ConfigInterface $config
     * @param DefinitionsCollection $definitionsCollection
     *
     * @return void
     * @since 2.0.0
     */
    private function fillThirdPartyInterfaces(ConfigInterface $config, DefinitionsCollection $definitionsCollection)
    {
        $definedInstances = $definitionsCollection->getInstancesNamesList();

        foreach (array_keys($config->getPreferences()) as $interface) {
            if (in_array($interface, $definedInstances)) {
                continue;
            }

            $definitionsCollection->addDefinition($interface, []);
        }
    }
}
