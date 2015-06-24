<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Magento\Setup\Model\Updater as ModelUpdater;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controller for updater tasks
 */
class ComponentUpgrade extends AbstractActionController
{
    /**
     * @var ModelUpdater
     */
    private $updater;

    /**
     * @param ModelUpdater $updater
     */
    public function __construct(ModelUpdater $updater)
    {
        $this->updater = $updater;
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
     * Update action
     *
     * @return JsonModel
     */
    public function updateAction()
    {
        $package = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $errorMessage = '';
        if (isset($package['name']) && isset($package['version'])) {
            $errorMessage .= $this->updater->createUpdaterTask(
                [['name' => $package['name'], 'version' => $package['version']]]
            );
        } else {
            $errorMessage .= 'Missing package information';
        }
        $success = empty($errorMessage) ? true : false;
        return new JsonModel(['success' => $success, 'message' => $errorMessage]);
    }
}
