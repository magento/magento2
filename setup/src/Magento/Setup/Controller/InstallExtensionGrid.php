<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\PackagesData;

/**
 * Controller for extensions grid tasks
 * @since 2.0.0
 */
class InstallExtensionGrid extends AbstractActionController
{
    /**
     * @var PackagesData
     * @since 2.1.0
     */
    private $packagesData;

    /**
     * @param PackagesData $packagesData
     * @since 2.0.0
     */
    public function __construct(
        PackagesData $packagesData
    ) {
        $this->packagesData = $packagesData;
    }

    /**
     * Index page action
     *
     * @return ViewModel
     * @since 2.0.0
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Get Extensions info action
     *
     * @return JsonModel
     * @since 2.0.0
     */
    public function extensionsAction()
    {
        $extensions = $this->packagesData->getPackagesForInstall();
        $packages = isset($extensions['packages']) ? $extensions['packages'] : [];
        $packages = $this->formatPackageList($packages);

        return new JsonModel(
            [
                'success' => true,
                'extensions' => array_values($packages),
                'total' => count($packages)
            ]
        );
    }

    /**
     * Format package list
     *
     * @param array $packages
     * @return array
     * @since 2.2.0
     */
    private function formatPackageList(array $packages)
    {
        array_walk($packages, function (&$package) {
            $package['vendor'] = ucfirst($package['vendor']);
        });

        return $packages;
    }
}
