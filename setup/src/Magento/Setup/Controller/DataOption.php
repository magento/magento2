<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\UninstallCollector;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controller of data option selection
 * @since 2.0.0
 */
class DataOption extends AbstractActionController
{
    /**
     * @var UninstallCollector
     * @since 2.0.0
     */
    private $uninstallCollector;

    /**
     * Constructor
     *
     * @param UninstallCollector $uninstallCollector
     * @since 2.0.0
     */
    public function __construct(UninstallCollector $uninstallCollector)
    {
        $this->uninstallCollector = $uninstallCollector;
    }

    /**
     * Shows data option page
     *
     * @return ViewModel|\Zend\Http\Response
     * @since 2.0.0
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/data-option.phtml');
        return $view;
    }

    /**
     * Checks if module has uninstall class
     *
     * @return JsonModel
     * @since 2.0.0
     */
    public function hasUninstallAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        if (isset($params['moduleName'])) {
            $uninstallClasses = $this->uninstallCollector->collectUninstall([$params['moduleName']]);
        }
        return new JsonModel(['hasUninstall' => isset($uninstallClasses) && sizeof($uninstallClasses) > 0]);
    }
}
