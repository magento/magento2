<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class \Magento\Setup\Controller\CreateAdminAccount
 *
 * @since 2.0.0
 */
class CreateAdminAccount extends AbstractActionController
{
    /**
     * @return ViewModel
     * @since 2.0.0
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        return $view;
    }
}
