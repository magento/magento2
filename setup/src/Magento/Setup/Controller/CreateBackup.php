<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * CreateBackup controller
 */
class CreateBackup extends AbstractActionController
{
    /**
     * Index action
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }
}
