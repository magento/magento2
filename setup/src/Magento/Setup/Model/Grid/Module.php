<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Grid;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\PackagesData;

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
            $this->getModuleListFromComposer(),
            $this->getFullModuleList()
        );

        $items = $this->addRequiredBy($this->addGeneralInfo($items));

        return $items;
    }

    /**
     * Get module list from composer
     *
     * @return array
     */
    private function getModuleListFromComposer()
    {
        return array_filter(
            $this->composerInformation->getInstalledMagentoPackages(),
            function ($item) {
                return $item['type'] === ComposerInformation::MODULE_PACKAGE_TYPE;
            }
        );
    }

    /**
     * Get full module list
     *
     * @return array
     */
    private function getFullModuleList()
    {
        return $this->getModulesInfo(
            $this->fullModuleList->getNames()
        );
    }

    /**
     * Add all modules, extensions, metapackages a module required by
     *
     * @param array $items
     * @return array
     */
    private function addRequiredBy(array $items)
    {
        foreach ($items as $key => $item) {
            $items[$key]['requiredBy'] = $item['name'] != self::UNKNOWN_PACKAGE_NAME ?
                $this->addGeneralInfo(
                    $this->getModulesInfo(
                        $this->packageInfo->getRequiredBy($item['name'])
                    )
                ) : [];
        }

        return $items;
    }

    /**
     * Get modules info
     *
     * @param array $moduleList
     * @return array
     */
    private function getModulesInfo(array $moduleList)
    {
        $result = [];
        foreach ($moduleList as $moduleName) {
            $packageName = $this->packageInfo->getPackageName($moduleName);
            $key = $packageName ?: $moduleName;
            $result[$key] = [
                'name' => $packageName ?: self::UNKNOWN_PACKAGE_NAME,
                'moduleName' => $moduleName,
                'version' => $this->packageInfo->getVersion($moduleName) ?: self::UNKNOWN_VERSION,
            ];
        }

        return $result;
    }

    /**
     * Add general info to result array
     *
     * @param array $items
     * @return array
     */
    private function addGeneralInfo(array $items)
    {
        foreach ($items as &$item) {
            $item['moduleName'] = $item['moduleName'] ?: $this->packageInfo->getModuleName($item['name']);
            $item['enable'] = $this->moduleList->has($item['moduleName']);

            $vendorSource = $item['name'] == self::UNKNOWN_PACKAGE_NAME ? $item['moduleName'] : $item['name'];
            $item['vendor'] = ucfirst(current(preg_split('%[/_]%', $vendorSource)));

            $extraInfo = $this->packagesData->getPackageExtraInfo($item['name'], $item['version']);
            $item['product_name'] =  isset($extraInfo['x-magento-ext-title']) ?
                $extraInfo['x-magento-ext-title'] : $item['name'];
            $item['type'] = isset($extraInfo['x-magento-ext-type']) ? $extraInfo['x-magento-ext-type'] :
                $this->typeMapper->map($item['name'], ComposerInformation::MODULE_PACKAGE_TYPE);
        }

        return array_values($items);
    }
}
