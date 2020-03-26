<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * ReadinessCheckInstaller controller
 */
class ReadinessCheckInstaller extends AbstractActionController
{
    const INSTALLER = 'installer';

    /**
     * Index action
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/readiness-check.phtml');
        $view->setVariable('actionFrom', self::INSTALLER);
        return $view;
    }

    /**
     * Progress action
     *
     * @return array|ViewModel
     */
    public function progressAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/magento/setup/readiness-check/progress.phtml');
        $view->setTerminal(true);
        return $view;
    }
}
