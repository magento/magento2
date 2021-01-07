<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Collects all ConfigOptionsList class in modules and setup
 */
class ConfigOptionsListCollector
{
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Service locator
     *
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Component list
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ObjectManagerProvider $objectManagerProvider
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        DirectoryList $directoryList,
        Filesystem $filesystem,
        ComponentRegistrarInterface $componentRegistrar,
        ObjectManagerProvider $objectManagerProvider,
        ServiceLocatorInterface $serviceLocator
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->objectManagerProvider = $objectManagerProvider;
        $this->serviceLocator = $serviceLocator;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Auto discover ConfigOptionsList class and collect them.
     *
     * These classes should reside in <module>/Setup directories.
     *
     * @return ConfigOptionsListInterface[]
     * @throws \Magento\Setup\Exception
     */
    public function collectOptionsLists()
    {
        $optionsList = [];

        $modulePaths = $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE);
        foreach (array_keys($modulePaths) as $moduleName) {
            $optionsClassName = str_replace('_', '\\', $moduleName) . '\Setup\ConfigOptionsList';
            if (class_exists($optionsClassName)) {
                $optionsClass = $this->objectManagerProvider->get()->create($optionsClassName);
                if ($optionsClass instanceof ConfigOptionsListInterface) {
                    $optionsList[$moduleName] = $optionsClass;
                }
            }
        }

        // check Setup
        $setupOptionsClassName = \Magento\Setup\Model\ConfigOptionsList::class;
        if (class_exists($setupOptionsClassName)) {
            $setupOptionsClass = $this->serviceLocator->get($setupOptionsClassName);
            if ($setupOptionsClass instanceof ConfigOptionsListInterface) {
                $optionsList['setup'] = $setupOptionsClass;
            }
        }

        return $optionsList;
    }
}
