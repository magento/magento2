<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\MaintenanceMode;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class CompleteBackup extends AbstractActionController
{
    /**
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTemplate('/error/404.phtml');
        $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_404);
        return $view;
    }

    /**
     * @return array|ViewModel
     */
    public function progressAction()
    {
        $view = new ViewModel;
        $view->setTemplate('/magento/setup/complete-backup/progress.phtml');
        $view->setTerminal(true);
        return $view;
    }
}
