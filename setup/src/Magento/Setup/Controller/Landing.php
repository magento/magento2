<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * Controller for Setup Landing page
 */
class Landing extends AbstractActionController
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
     * Setup index action.
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/landing.phtml');
        $view->setVariable('version', $this->productMetadata->getVersion());
        return $view;
    }
}
