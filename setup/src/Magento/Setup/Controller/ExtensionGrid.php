<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\PackagesAuth;
use Magento\Setup\Model\PackagesData;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controller for extension grid tasks
 */
class ExtensionGrid extends AbstractActionController
{
    /**
     * @var \Magento\Framework\Composer\ComposerInformation
     */
    private $composerInformation;

    /**
     * @var PackagesData
     */
    private $packagesData;

    /**
     * @var PackagesAuth
     */
    private $packagesAuth;

    /**
     * @param ComposerInformation $composerInformation
     * @param PackagesData $packagesData
     * @param PackagesAuth $packagesAuth
     */
    public function __construct(
        ComposerInformation $composerInformation,
        \Magento\Setup\Model\PackagesData $packagesData,
        \Magento\Setup\Model\PackagesAuth $packagesAuth
    ) {
        $this->composerInformation = $composerInformation;
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
     * Get extensions info action
     *
     * @return JsonModel
     * @throws \RuntimeException
     */
    public function extensionsAction()
    {
        $error = '';
        $lastSyncData = [];
        $authDetails = $this->packagesAuth->getAuthJsonData();
        if ($authDetails) {
            try {
                $lastSyncData = $this->packagesData->syncPackagesData();
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $extensions = $this->packagesData->getInstalledExtensions();

        foreach ($extensions as &$extension) {
            $extension['update'] = false;
            $extension['uninstall'] = false;
            if ($this->composerInformation->isPackageInComposerJson($extension['name'])) {
                $extension['uninstall'] = true;
                if (isset($lastSyncData['packages'][$extension['name']]['latestVersion'])
                    && version_compare(
                        $lastSyncData['packages'][$extension['name']]['latestVersion'],
                        $extension['version'],
                        '>'
                    )) {
                    $extension['update'] = true;
                }
            }
            $parts = explode('/', $extension['name']);
            $extension['vendor'] = $parts[0];
        }

        return new JsonModel(
            [
                'success' => true,
                'extensions' => array_values($extensions),
                'total' => count($extensions),
                'lastSyncData' => $lastSyncData,
                'error' => $error
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
        $error = '';
        $lastSyncData = [];
        try {
            $lastSyncData = $this->packagesData->syncPackagesData();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        return new JsonModel(
            [
                'success' => true,
                'lastSyncData' => $lastSyncData,
                'error' => $error
            ]
        );
    }
}
