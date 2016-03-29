<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Controller for Updater Landing page
 */
class LandingUpdater extends AbstractActionController
{
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     */
    public function __construct(\Magento\Framework\App\ProductMetadata $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $welcomeMsg = "Welcome to Magento Component Manager.<br>"
            . "Click 'Agree and Update Magento' or read ";
        $docRef = "http://devdocs.magento.com/guides/v1.0/install-gde/install/install-web.html";
        $agreeButtonText = "Agree and Update Magento";
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/landing.phtml');
        $view->setVariable('version', $this->productMetadata->getVersion());
        $view->setVariable('welcomeMsg', $welcomeMsg);
        $view->setVariable('docRef', $docRef);
        $view->setVariable('agreeButtonText', $agreeButtonText);
        return $view;
    }
}
