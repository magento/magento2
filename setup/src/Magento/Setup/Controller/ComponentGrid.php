<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\ModuleList;
use Magento\Setup\Model\ObjectManagerProvider;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

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
     * Module  info
     *
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @param ComposerInformation $composerInformation
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(
        ComposerInformation $composerInformation,
        ObjectManagerProvider $objectManagerProvider
    ) {
        $this->composerInformation = $composerInformation;
        $objectManager = $objectManagerProvider->get();
        $this->packageInfo = $objectManager->get('Magento\Framework\Module\PackageInfoFactory')->create();
        $this->moduleList = $objectManager->get('Magento\Framework\Module\ModuleList');
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
        $lastSyncData = $this->composerInformation->getPackagesForUpdate();
        $components = $this->composerInformation->getInstalledMagentoPackages();
        foreach ($components as $component) {
            $components[$component['name']]['update'] = false;
            $components[$component['name']]['uninstall'] = false;
            $components[$component['name']]['moduleName'] = $this->packageInfo->getModuleName($component['name']);
            if ($this->composerInformation->isPackageInComposerJson($component['name'])
                && ($component['type'] !== ComposerInformation::METAPACKAGE_PACKAGE_TYPE)) {
                $components[$component['name']]['uninstall'] = true;
                $components[$component['name']]['enable'] =
                    $this->moduleList->has($components[$component['name']]['moduleName']);
                if (isset($lastSyncData['packages'][$component['name']]['latestVersion'])
                    && version_compare(
                        $lastSyncData['packages'][$component['name']]['latestVersion'],
                        $component['version'],
                        '>'
                    )) {
                    $components[$component['name']]['update'] = true;
                }
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
        $this->composerInformation->syncPackagesForUpdate();
        $lastSyncData = $this->composerInformation->getPackagesForUpdate();
        return new JsonModel(
            [
                'success' => true,
                'lastSyncData' => $lastSyncData
            ]
        );
    }
}
