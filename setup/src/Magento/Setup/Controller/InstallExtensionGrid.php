<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\MarketplaceManager;

/**
 * Controller for extensions grid tasks
 */
class InstallExtensionGrid extends AbstractActionController
{
    /**
     * @var MarketplaceManager
     */
    private $marketplaceManager;

    /**
     * @param MarketplaceManager $marketplaceManager
     */
    public function __construct(MarketplaceManager $marketplaceManager)
    {
        $this->marketplaceManager = $marketplaceManager;
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
        $extensions = $this->getMarketplaceManager()->getPackagesForInstall();
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
     * @return MarketplaceManager
     */

    public function getMarketplaceManager()
    {
        return $this->marketplaceManager;
    }
}
