<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\PackagesData;
use \Magento\Setup\Model\Grid\TypeMapper;

/**
 * Controller for extensions grid tasks
 */
class InstallExtensionGrid extends AbstractActionController
{
    /**
     * @var PackagesData
     */
    private $packagesData;

    /**
     * @var TypeMapper
     */
    private $typeMapper;

    /**
     * @param PackagesData $packagesData
     * @param TypeMapper $typeMapper
     */
    public function __construct(
        PackagesData $packagesData,
        TypeMapper $typeMapper
    ) {
        $this->packagesData = $packagesData;
        $this->typeMapper = $typeMapper;
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
     * Get Extensions info action
     *
     * @return JsonModel
     */
    public function extensionsAction()
    {
        $extensions = $this->packagesData->getPackagesForInstall();
        $packages = isset($extensions['packages']) ? $extensions['packages'] : [];
        array_walk($packages, function (&$package) {
            $package['vendor'] = ucfirst($package['vendor']);
            $package['type'] =  $this->typeMapper->map($package['name'], $package['type']);
        });

        return new JsonModel(
            [
                'success' => true,
                'extensions' => array_values($packages),
                'total' => count($packages)
            ]
        );
    }
}
