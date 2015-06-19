<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\AppInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Controller for Setup Landing page
 */
class Landing extends AbstractActionController
{
    /**
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('languages', $this->serviceLocator->get('config')['languages']);
        $view->setVariable('location', 'en_US');
        $view->setVariable('version', AppInterface::VERSION);
        return $view;
    }
}
