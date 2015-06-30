<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controller for component grid tasks
 */
class ComponentGrid extends AbstractActionController
{
    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * Index page action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * @return JsonModel
     */
    public function componentsAction()
    {
        $components = [
          [
            'name' => 'bluesky-theme',
            'type' => 'magento/theme',
            'version' => '1.0.0',
          ],
          [
            'name' => 'greenfield-theme',
            'type' => 'magento/theme',
            'version' => '0.9.0',
          ],
          [
            'name' => 'redlava-module',
            'type' => 'magento/module',
            'version' => '2.0.1',
          ],
        ];
        return new JsonModel(['success' => true, 'components' => $components]);
    }
}
