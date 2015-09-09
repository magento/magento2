<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\PackageInfo;
use Magento\Setup\Model\ObjectManagerProvider;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\UpdatePackagesCache;

/**
 * Controller for component grid tasks
 */
class ComponentGrid extends AbstractActionController
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
     * Enabled Module info
     *
     * @var ModuleList
     */
    private $enabledModuleList;

    /**
     * Full Module info
     *
     * @var FullModuleList
     */
    private $fullModuleList;

    /**
     * @var UpdatePackagesCache
     */
    private $updatePackagesCache;

    /**
     * @param ComposerInformation $composerInformation
     * @param ObjectManagerProvider $objectManagerProvider
     * @param UpdatePackagesCache $updatePackagesCache
     */
    public function __construct(
        ComposerInformation $composerInformation,
        ObjectManagerProvider $objectManagerProvider,
        UpdatePackagesCache $updatePackagesCache
    ) {
        $this->composerInformation = $composerInformation;
        $objectManager = $objectManagerProvider->get();
        $this->enabledModuleList = $objectManager->get('Magento\Framework\Module\ModuleList');
        $this->fullModuleList = $objectManager->get('Magento\Framework\Module\FullModuleList');
        $this->packageInfo = $objectManagerProvider->get()
            ->get('Magento\Framework\Module\PackageInfoFactory')->create();
        $this->updatePackagesCache = $updatePackagesCache;
    }

    /**
     * Index page action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Get Components info action
     *
     * @return JsonModel
     * @throws \RuntimeException
     */
    public function componentsAction()
    {
        $lastSyncData = $this->updatePackagesCache->getPackagesForUpdate();
        $components = $this->composerInformation->getInstalledMagentoPackages();
        $allModules = $this->getAllModules();
        $components = array_replace_recursive($components, $allModules);
        foreach ($components as $component) {
            $components[$component['name']]['update'] = false;
            $components[$component['name']]['uninstall'] = false;
            $components[$component['name']]['moduleName'] = $this->packageInfo->getModuleName($component['name']);
            if ($this->composerInformation->isPackageInComposerJson($component['name'])
                && ($component['type'] !== ComposerInformation::METAPACKAGE_PACKAGE_TYPE)) {
                $components[$component['name']]['uninstall'] = true;
                if (isset($lastSyncData['packages'][$component['name']]['latestVersion'])
                    && version_compare(
                        $lastSyncData['packages'][$component['name']]['latestVersion'],
                        $component['version'],
                        '>'
                    )) {
                    $components[$component['name']]['update'] = true;
                }
            }
            if ($component['type'] === ComposerInformation::MODULE_PACKAGE_TYPE) {
                $components[$component['name']]['enable'] =
                    $this->enabledModuleList->has($components[$component['name']]['moduleName']);
                $components[$component['name']]['disable'] = !$components[$component['name']]['enable'];
            } else {
                $components[$component['name']]['enable'] = false;
                $components[$component['name']]['disable'] = false;
            }
            $componentNameParts = explode('/', $component['name']);
            $components[$component['name']]['vendor'] = $componentNameParts[0];
        }
        return new JsonModel(
            [
                'success' => true,
                'components' => array_values($components),
                'total' => count($components),
                'lastSyncData' => $lastSyncData
            ]
        );
    }

    /**
     * Sync action
     *
     * @return JsonModel
     */
    public function syncAction()
    {
        $this->updatePackagesCache->syncPackagesForUpdate();
        $lastSyncData = $this->updatePackagesCache->getPackagesForUpdate();
        return new JsonModel(
            [
                'success' => true,
                'lastSyncData' => $lastSyncData
            ]
        );
    }

    /**
     * Get full list of modules as an associative array
     *
     * @return array
     */
    private function getAllModules()
    {
        $modules = [];
        $allModules = $this->fullModuleList->getNames();
        foreach ($allModules as $module) {
            $moduleName = $this->packageInfo->getPackageName($module);
            $modules[$moduleName]['name'] = $moduleName;
            $modules[$moduleName]['type'] = ComposerInformation::MODULE_PACKAGE_TYPE;
            $modules[$moduleName]['version'] = $this->packageInfo->getVersion($module);
        }
        return $modules;
    }
}
