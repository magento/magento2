<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * CompleteBackup controller
 */
class CompleteBackup extends AbstractActionController
{
    /**
     * Index action
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTemplate('/error/404.phtml');
        $this->getResponse()->setStatusCode(\Laminas\Http\Response::STATUS_CODE_404);
        return $view;
    }

    /**
     * Progress action
     *
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
