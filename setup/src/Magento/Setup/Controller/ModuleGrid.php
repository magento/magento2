<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Controller for module grid tasks
 */
class ModuleGrid extends \Zend\Mvc\Controller\AbstractActionController
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
     * @param ComposerInformation $composerInformation
     * @param FullModuleList $fullModuleList
     * @param ModuleList $moduleList
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        ComposerInformation $composerInformation,
        FullModuleList $fullModuleList,
        ModuleList $moduleList,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->composerInformation = $composerInformation;
        $this->fullModuleList = $fullModuleList;
        $this->moduleList = $moduleList;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Index page action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view = new \Zend\View\Model\ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Get Components info action
     *
     * @return \Zend\View\Model\JsonModel
     * @throws \RuntimeException
     */
    public function modulesAction()
    {
        $this->packageInfo = $this->objectManagerProvider->get()
            ->get(PackageInfoFactory::class)
            ->create();

        $moduleList = $this->getModuleList();

        return new \Zend\View\Model\JsonModel(
            [
                'success' => true,
                'modules' => $moduleList,
                'total' => count($moduleList),
            ]
        );
    }

    /**
     * Get list of installed modules (composer + direct installation)
     *
     * @return array
     */
    private function getModuleList()
    {
        $items = array_replace_recursive(
            $this->composerInformation->getInstalledMagentoPackages(),
            $this->getInstalledModules()
        );

        $items = array_filter($items, function ($item) {
            return $item['type'] === ComposerInformation::MODULE_PACKAGE_TYPE;
        });

        array_walk($items, function (&$module, $name) {
            $module['moduleName'] = isset($module['moduleName']) ?
                $module['moduleName'] : $this->packageInfo->getModuleName($name);
            $module['enable'] = $this->moduleList->has($module['moduleName']);
            $module['vendor'] = current(explode('/', $name));
            $module['type'] = str_replace('magento2-', '', $module['type']);
            $module['requiredBy'] = $this->packageInfo->getRequiredBy($name);
        });

        return array_values($items);
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
