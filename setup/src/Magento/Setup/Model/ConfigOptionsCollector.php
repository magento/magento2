<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;

/**
 * Collects all ConfigOptions class in modules and setup
 */
class ConfigOptionsCollector
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
     * Enabled module list
     *
     * @var ModuleList
     */
    private $moduleList;

    /**
     * Object manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Constructor
     *
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param FullModuleList $fullModuleList
     * @param ModuleList $moduleList
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        DirectoryList $directoryList,
        Filesystem $filesystem,
        FullModuleList $fullModuleList,
        ModuleList $moduleList,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->fullModuleList = $fullModuleList;
        $this->moduleList = $moduleList;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Auto discover Options class and collect their options
     *
     * @return array
     */
    public function collectOptions()
    {
        $optionsList = [];

        // go through modules
        foreach ($this->fullModuleList->getNames() as $moduleName) {
            $optionsClassName = str_replace('_', '\\', $moduleName) . '\Setup\ConfigOptions';
            if (class_exists($optionsClassName)) {
                $optionsClass = $this->objectManagerProvider->get()->create($optionsClassName);
                if ($optionsClass instanceof \Magento\Framework\Setup\ConfigOptionsInterface) {
                    $optionsList[$optionsClassName] = [
                        'options' => $optionsClass->getOptions(),
                        'enabled' => $this->moduleList->has($moduleName),
                    ];
                }
            }
        }

        // check setup
        $setupOptionsClassName = 'Magento\Setup\Model\ConfigOptions';
        if (class_exists($setupOptionsClassName)) {
            $setupOptionsClass = $this->objectManagerProvider->get()->create($setupOptionsClassName);
            if ($setupOptionsClass instanceof \Magento\Framework\Setup\ConfigOptionsInterface) {
                $optionsList[$setupOptionsClassName] = [
                    'options' => $setupOptionsClass->getOptions(),
                    'enabled' => true,
                ];
            }
        }

        return $optionsList;
    }
}
