<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\PackagesData;

/**
 * Module grid
 */
class Module
{
    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * Module package info
     *
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Full Module info
     *
     * @var FullModuleList
     */
    private $fullModuleList;

    /**
     * Module info
     *
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @var PackagesData
     */
    private $packagesData;

    /**
     * @param ComposerInformation $composerInformation
     * @param FullModuleList $fullModuleList
     * @param ModuleList $moduleList
     * @param ObjectManagerProvider $objectManagerProvider
     * @param TypeMapper $typeMapper
     * @param PackagesData $packagesData
     */
    public function __construct(
        ComposerInformation $composerInformation,
        FullModuleList $fullModuleList,
        ModuleList $moduleList,
        ObjectManagerProvider $objectManagerProvider,
        TypeMapper $typeMapper,
        PackagesData $packagesData
    ) {
        $this->composerInformation = $composerInformation;
        $this->fullModuleList = $fullModuleList;
        $this->moduleList = $moduleList;
        $this->objectManagerProvider = $objectManagerProvider;
        $this->typeMapper = $typeMapper;
        $this->packagesData = $packagesData;
    }

    /**
     * Get list of installed modules (composer + direct installation)
     *
     * @return array
     */
    public function getList()
    {
        $this->packageInfo = $this->objectManagerProvider->get()
            ->get(PackageInfoFactory::class)
            ->create();
        
        $items = array_replace_recursive(
            $this->composerInformation->getInstalledMagentoPackages(),
            $this->getInstalledModules()
        );

        $items = array_filter($items, function ($item) {
            return $item['type'] === ComposerInformation::MODULE_PACKAGE_TYPE;
        });

        array_walk($items, function (&$module, $name) {
            $module['moduleName'] = $this->packageInfo->getModuleName($name);
            $module['enable'] = $this->moduleList->has($module['moduleName']);
            $module['vendor'] = ucfirst(current(explode('/', $name)));
            $module['type'] = $this->typeMapper->map($name, $module['type']);
            $module['requiredBy'] = $this->getModuleRequiredBy($name);
        });

        return array_values($items);
    }

    /**
     * Get all modules, extensions, metapackages a module required by
     * 
     * @param string $name Module name
     * @return array
     */
    private function getModuleRequiredBy($name)
    {
        $result = [];
        $modules = $this->packageInfo->getRequiredBy($name);
        foreach ($modules as $moduleName) {
            $packageName = $this->packageInfo->getPackageName($moduleName);
            $result[] = [
                'name' => $packageName,
                'moduleName' => $moduleName,
                'type' => $this->typeMapper->map($packageName, ComposerInformation::MODULE_PACKAGE_TYPE),
                'enable' => $this->moduleList->has($moduleName),
                'version' => $this->packageInfo->getVersion($moduleName)
            ];
        }

        return $result;
    }

    /**
     * Get full list of installed modules
     *
     * @return array
     */
    private function getInstalledModules()
    {
        $modules = [];
        $allModules = $this->fullModuleList->getNames();
        foreach ($allModules as $module) {
            $name = $this->packageInfo->getPackageName($module);
            $modules[$name]['name'] = $name;
            $modules[$name]['type'] = ComposerInformation::MODULE_PACKAGE_TYPE;
            $modules[$name]['version'] = $this->packageInfo->getVersion($module);
        }

        return $modules;
    }
}
