<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class \Magento\Setup\Controller\ReadinessCheckUpdater
 *
 * @since 2.0.0
 */
class ReadinessCheckUpdater extends AbstractActionController
{
    const UPDATER = 'updater';

    /**
     * @return array|ViewModel
     * @since 2.0.0
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/readiness-check.phtml');
        $view->setVariable('actionFrom', self::UPDATER);
        return $view;
    }

    /**
     * @return array|ViewModel
     * @since 2.0.0
     */
    public function progressAction()
    {
        $view = new ViewModel;
        $view->setTemplate('/magento/setup/readiness-check/progress.phtml');
        $view->setTerminal(true);
        return $view;
    }
}
