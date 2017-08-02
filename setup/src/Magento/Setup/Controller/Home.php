<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Controller of homepage of setup
 * @since 2.0.0
 */
class Home extends AbstractActionController
{
    /**
     * @return ViewModel|\Zend\Http\Response
     * @since 2.0.0
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/home.phtml');
        $view->setVariable('userName', 'UserName');
        return $view;
    }
}
