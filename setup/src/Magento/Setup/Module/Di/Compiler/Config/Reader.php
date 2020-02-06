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
 *
 * @package Magento\Setup\Module\Di\Compiler\Config
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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

        // sort configuration to have it in the same order on every build
        ksort($config['arguments']);
        ksort($config['preferences']);
        ksort($config['instanceTypes']);

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
        foreach ($definitionsCollection->getCollection() as $instanceType => $constructor) {
            if (!$this->typeReader->isConcrete($instanceType)) {
                continue;
            }
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
     */
    private function fillThirdPartyInterfaces(ConfigInterface $config, DefinitionsCollection $definitionsCollection)
    {
        $definedInstances = $definitionsCollection->getCollection();
        $newInstances = array_fill_keys(array_keys($config->getPreferences()), []);
        $newCollection = array_merge($newInstances, $definedInstances);
        $definitionsCollection->initialize($newCollection);
    }
}
