<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Composer\InfoCommand;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Module\PackageInfo;
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
     * @var InfoCommand
     */
    private $infoCommand;

    /**
     * @param ComposerInformation $composerInformation
     * @param ObjectManagerProvider $objectManagerProvider
     * @param MagentoComposerApplicationFactory $magentoComposerApplicationFactory
     */
    public function __construct(
        ComposerInformation $composerInformation,
        ObjectManagerProvider $objectManagerProvider,
        MagentoComposerApplicationFactory $magentoComposerApplicationFactory
    ) {
        $this->composerInformation = $composerInformation;
        $this->packageInfo = $objectManagerProvider->get()
            ->get('Magento\Framework\Module\PackageInfoFactory')->create();
        $this->infoCommand = $magentoComposerApplicationFactory->createInfoCommand();
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
        $components = $this->composerInformation->getInstalledMagentoPackages();
        foreach ($components as $component) {
            if ($this->composerInformation->isPackageInComposerJson($component['name'])) {
                $packageInfo = $this->infoCommand->run($component['name']);
                if (!$packageInfo) {
                    throw new \RuntimeException('Package info not found for ' . $component['name']);
                }
                if (empty($packageInfo[InfoCommand::NEW_VERSIONS])) {
                    unset($components[$component['name']]);
                    continue;
                }
                $componentNameParts = explode('/', $component['name']);
                $components[$component['name']]['vendor'] = $componentNameParts[0];
                $components[$component['name']]['moduleName'] = $this->packageInfo->getModuleName($component['name']);
                $components[$component['name']]['update'] = true;
                $components[$component['name']]['uninstall'] = true;
            }
        }
        $lastSyncData = $this->composerInformation->getPackagesForUpdate();
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
