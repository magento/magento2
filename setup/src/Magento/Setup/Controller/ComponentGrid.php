<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\DateTime\TimezoneProvider;

/**
 * Controller for component grid tasks
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Setup\Model\MarketplaceManager
     */
    private $marketplaceManager;

    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    private $enabledModuleList;

    /**
     * Full Module info
     *
     * @var \Magento\Framework\Module\FullModuleList
     */
    private $fullModuleList;

    /**
     * @var \Magento\Setup\Model\UpdatePackagesCache
     */
    private $updatePackagesCache;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\Composer\ComposerInformation $composerInformation
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param \Magento\Setup\Model\UpdatePackagesCache $updatePackagesCache
     * @param \Magento\Setup\Model\MarketplaceManager $marketplaceManager
     * @param TimezoneProvider $timezoneProvider
     */
    public function __construct(
        \Magento\Framework\Composer\ComposerInformation $composerInformation,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        \Magento\Setup\Model\UpdatePackagesCache $updatePackagesCache,
        \Magento\Setup\Model\MarketplaceManager $marketplaceManager,
        \Magento\Setup\Model\DateTime\TimezoneProvider $timezoneProvider
    ) {
        $this->composerInformation = $composerInformation;
        $this->objectManager = $objectManagerProvider->get();
        $this->enabledModuleList = $this->objectManager->get('Magento\Framework\Module\ModuleList');
        $this->fullModuleList = $this->objectManager->get('Magento\Framework\Module\FullModuleList');
        $this->packageInfo = $this->objectManager->get('Magento\Framework\Module\PackageInfoFactory')->create();
        $this->marketplaceManager = $marketplaceManager;
        $this->updatePackagesCache = $updatePackagesCache;
        $this->timezone = $timezoneProvider->get();
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
                    $this->enabledModuleList->has($components[$component['name']]['moduleName']);
                $components[$component['name']]['disable'] = !$components[$component['name']]['enable'];
            } else {
                $components[$component['name']]['enable'] = false;
                $components[$component['name']]['disable'] = false;
            }
            $componentNameParts = explode('/', $component['name']);
            $components[$component['name']]['vendor'] = $componentNameParts[0];
        }

        $packagesForInstall = $this->marketplaceManager->getPackagesForInstall();
        $lastSyncData = $this->formatLastSyncData($packagesForInstall, $lastSyncData);

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
        $packagesForInstall = [];
        $lastSyncData = [];
        try {
            $this->updatePackagesCache->syncPackagesForUpdate();
            $lastSyncData = $this->updatePackagesCache->getPackagesForUpdate();

            $this->marketplaceManager->syncPackagesForInstall();
            $packagesForInstall = $this->marketplaceManager->getPackagesForInstall();
            $lastSyncData = $this->formatLastSyncData($packagesForInstall, $lastSyncData);
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

    /**
     * Format the lastSyncData for use on frontend
     *
     * @param array $packagesForInstall
     * @param array $lastSyncData
     * @return mixed
     */
    private function formatLastSyncData($packagesForInstall, $lastSyncData)
    {
        $lastSyncData['countOfInstall']
            = isset($packagesForInstall['packages']) ? count($packagesForInstall['packages']) : 0;
        $lastSyncData['countOfUpdate'] = isset($lastSyncData['packages']) ? count($lastSyncData['packages']) : 0;
        if (isset($lastSyncData['lastSyncDate'])) {
            $lastSyncData['lastSyncDate'] = $this->formatSyncDate($lastSyncData['lastSyncDate']);
        }
        return $lastSyncData;
    }

    /**
     * Format a UTC timestamp (seconds since epoch) to structure expected by frontend
     *
     * @param string $syncDate seconds since epoch
     * @return array
     */
    private function formatSyncDate($syncDate)
    {
        return [
            'date' => $this->timezone->formatDateTime(
                new \DateTime('@'.$syncDate),
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE
            ),
            'time' => $this->timezone->formatDateTime(
                new \DateTime('@'.$syncDate),
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::MEDIUM
            ),
        ];
    }
}
