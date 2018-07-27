<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MarketplaceCredentials extends AbstractActionController
{
    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        return $view;
    }
}
