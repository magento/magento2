<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\ConnectManager;

/**
 * Controller for extensions grid tasks
 */
class InstallExtensionGrid extends AbstractActionController
{
    /**
     * @var ConnectManager
     */
    private $connectManager;

    /**
     * @param ConnectManager $connectManager
     */
    public function __construct(ConnectManager $connectManager)
    {
        $this->connectManager = $connectManager;
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
        $extensions = $this->getConnectManager()->getPackagesForInstall();
        $packages = isset($extensions['packages']) ? $extensions['packages'] : [];
        return new JsonModel(
            [
                'success' => true,
                'extensions' => array_values($packages),
                'total' => count($packages)
            ]
        );
    }

    /**
     * @return ConnectManager
     */

    public function getConnectManager()
    {
        return $this->connectManager;
    }
}
