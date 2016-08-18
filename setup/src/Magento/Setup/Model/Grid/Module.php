<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;

/**
 * Module grid
 */
class Module
{
    /**
     * Const for unknown package name and version
     */
    const UNKNOWN_PACKAGE_NAME = 'unknown';
    const UNKNOWN_VERSION = '—';

    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * Module package info
     *
     * @var \Magento\Framework\Module\PackageInfo
     */
    private $packageInfo;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Full Module info
     *
     * @var \Magento\Framework\Module\FullModuleList
     */
    private $fullModuleList;

    /**
     * Module info
     *
     * @var \Magento\Framework\Module\ModuleList
     */
    private $moduleList;

    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @var \Magento\Setup\Model\PackagesData
     */
    private $packagesData;

    /**
     * @param ComposerInformation $composerInformation
     * @param \Magento\Framework\Module\FullModuleList $fullModuleList
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param TypeMapper $typeMapper
     * @param \Magento\Setup\Model\PackagesData $packagesData
     */
    public function __construct(
        ComposerInformation $composerInformation,
        \Magento\Framework\Module\FullModuleList $fullModuleList,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        TypeMapper $typeMapper,
        \Magento\Setup\Model\PackagesData $packagesData
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
            ->get(\Magento\Framework\Module\PackageInfoFactory::class)
            ->create();

        $items = array_replace_recursive(
            $this->composerInformation->getInstalledMagentoPackages(),
            $this->getInstalledModules()
        );

        $items = array_filter($items, function ($item) {
            return $item['type'] === ComposerInformation::MODULE_PACKAGE_TYPE;
        });

        array_walk($items, function (&$module, $name) {
            $module['moduleName'] = $module['moduleName'] ?: $this->packageInfo->getModuleName($name);
            $module['enable'] = $this->moduleList->has($module['moduleName']);
            $module['vendor'] = ucfirst(current(preg_split('%[/_]%', $name)));
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
                'name' => $packageName ?: self::UNKNOWN_PACKAGE_NAME,
                'moduleName' => $moduleName,
                'type' => $this->typeMapper->map($packageName, ComposerInformation::MODULE_PACKAGE_TYPE),
                'enable' => $this->moduleList->has($moduleName),
                'version' => $this->packageInfo->getVersion($moduleName) ?: self::UNKNOWN_VERSION,
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
            $packageName = $this->packageInfo->getPackageName($module);
            $name = $packageName ?: $module;
            $modules[$name]['name'] = $packageName ?: self::UNKNOWN_PACKAGE_NAME;
            $modules[$name]['moduleName'] = $module;
            $modules[$name]['type'] = ComposerInformation::MODULE_PACKAGE_TYPE;
            $modules[$name]['version'] = $this->packageInfo->getVersion($module) ?: self::UNKNOWN_VERSION;
        }

        return $modules;
    }
}
