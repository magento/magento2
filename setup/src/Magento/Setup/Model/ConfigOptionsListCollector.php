<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\FullModuleList;
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
     * Module list including enabled and disabled modules
     *
     * @var FullModuleList
     */
    private $fullModuleList;

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
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param FullModuleList $fullModuleList
     * @param ObjectManagerProvider $objectManagerProvider
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        DirectoryList $directoryList,
        Filesystem $filesystem,
        FullModuleList $fullModuleList,
        ObjectManagerProvider $objectManagerProvider,
        ServiceLocatorInterface $serviceLocator
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->fullModuleList = $fullModuleList;
        $this->objectManagerProvider = $objectManagerProvider;
        $this->serviceLocator = $serviceLocator;
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

        // go through modules
        foreach ($this->fullModuleList->getNames() as $moduleName) {
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
