<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Di\Compiler\Config;

use Magento\Framework\App;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\Phrase;
use Magento\Setup\Module\Di\Code\Reader\ClassReaderDecorator;
use Magento\Setup\Module\Di\Code\Reader\Type;
use Magento\Setup\Module\Di\Compiler\ArgumentsResolverFactory;
use Magento\Setup\Module\Di\Definition\Collection as DefinitionsCollection;

/**
 * DI Confir Reader
 *
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
                if (array_key_exists($preference, $areaConfig->getVirtualTypes())) {
                    // Special handling is required for virtual types.
                    $config['preferences'][$instanceName] = $preference;
                    continue;
                }

                if (!class_exists($preference)) {
                    throw new LocalizedException(new Phrase(
                        'Preference declared for "%instanceName" as "%preference", but the latter does not exist.',
                        [
                            'instanceName' => $instanceName,
                            'preference' => $preference,
                        ]
                    ));
                }

                // Classes defined by PHP extensions are allowed.
                $reflection = new \ReflectionClass($preference);
                if ($reflection->getExtension()) {
                    $config['preferences'][$instanceName] = $preference;
                    continue;
                }

                if (!$definitionsCollection->hasInstance($preference)) {
                    // See 'excludePatterns' in Magento\Setup\Module\Di\Code\Reader\ClassesScanner,
                    // populated via Magento\Setup\Console\Command\DiCompileCommand
                    throw new LocalizedException(new Phrase(
                        'Preference declared for "%instanceName" as "%preference", but the latter'
                            . ' has not been included in dependency injection compilation.',
                        [
                            'instanceName' => $instanceName,
                            'preference' => $preference,
                        ]
                    ));
                }

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
