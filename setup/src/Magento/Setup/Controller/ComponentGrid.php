<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\PackagesAuth;
use Magento\Setup\Model\PackagesData;

/**
 * Controller for component grid tasks
 */
class ComponentGrid extends \Zend\Mvc\Controller\AbstractActionController
{
    /**
     * @var \Magento\Framework\Composer\ComposerInformation
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
     * @var \Magento\Framework\Module\FullModuleList
     */
    private $fullModuleList;

    /**
     * @var PackagesData
     */
    private $packagesData;

    /**
     * @var PackagesAuth
     */
    private $packagesAuth;

    /**
     * @param \Magento\Framework\Composer\ComposerInformation $composerInformation
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param PackagesData $packagesData
     * @param PackagesAuth $packagesAuth
     */
    public function __construct(
        \Magento\Framework\Composer\ComposerInformation $composerInformation,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        \Magento\Setup\Model\PackagesData $packagesData,
        \Magento\Setup\Model\PackagesAuth $packagesAuth
    ) {
        $this->composerInformation = $composerInformation;
        $this->objectManagerProvider = $objectManagerProvider;
        $this->packagesData = $packagesData;
        $this->packagesAuth = $packagesAuth;
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
    public function componentsAction()
    {
        $objectManager = $this->objectManagerProvider->get();
        $enabledModuleList = $objectManager->get('Magento\Framework\Module\ModuleList');
        $this->fullModuleList = $objectManager->get('Magento\Framework\Module\FullModuleList');
        $this->packageInfo = $objectManager->get('Magento\Framework\Module\PackageInfoFactory')->create();

        $lastSyncData = [];
        $authDetails = $this->packagesAuth->getAuthJsonData();
        if ($authDetails) {
            $lastSyncData = $this->packagesData->syncPackagesData();
        }
        $components = $this->composerInformation->getInstalledMagentoPackages();
        $allModules = $this->getAllModules();
        $components = array_replace_recursive($components, $allModules);
        foreach ($components as $component) {
            $components[$component['name']]['update'] = false;
            $components[$component['name']]['uninstall'] = false;
            $components[$component['name']]['moduleName'] = $this->packageInfo->getModuleName($component['name']);
            if ($this->composerInformation->isPackageInComposerJson($component['name'])) {
                if ($component['type'] !== \Magento\Framework\Composer\ComposerInformation::METAPACKAGE_PACKAGE_TYPE) {
                    $components[$component['name']]['uninstall'] = true;
                }
                if (isset($lastSyncData['packages'][$component['name']]['latestVersion'])
                    && version_compare(
                        $lastSyncData['packages'][$component['name']]['latestVersion'],
                        $component['version'],
                        '>'
                    )) {
                    $components[$component['name']]['update'] = true;
                }
            }
            if ($component['type'] === \Magento\Framework\Composer\ComposerInformation::MODULE_PACKAGE_TYPE) {
                $components[$component['name']]['enable'] =
                    $enabledModuleList->has($components[$component['name']]['moduleName']);
                $components[$component['name']]['disable'] = !$components[$component['name']]['enable'];
            } else {
                $components[$component['name']]['enable'] = false;
                $components[$component['name']]['disable'] = false;
            }
            $componentNameParts = explode('/', $component['name']);
            $components[$component['name']]['vendor'] = $componentNameParts[0];
        }

        return new \Zend\View\Model\JsonModel(
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
     * @return \Zend\View\Model\JsonModel
     */
    public function syncAction()
    {
        $error = '';
        $lastSyncData = [];
        try {
            $lastSyncData = $this->packagesData->syncPackagesData();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        return new \Zend\View\Model\JsonModel(
            [
                'success' => true,
                'lastSyncData' => $lastSyncData,
                'error' => $error
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
            $modules[$moduleName]['type'] = \Magento\Framework\Composer\ComposerInformation::MODULE_PACKAGE_TYPE;
            $modules[$moduleName]['version'] = $this->packageInfo->getVersion($module);
        }
        return $modules;
    }
}
