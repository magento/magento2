<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\UninstallCollector;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

/**
 * Controller of data option selection
 */
class DataOption extends AbstractActionController
{
    /**
     * @var UninstallCollector
     */
    private $uninstallCollector;

    /**
     * Constructor
     *
     * @param UninstallCollector $uninstallCollector
     */
    public function __construct(UninstallCollector $uninstallCollector)
    {
        $this->uninstallCollector = $uninstallCollector;
    }

    /**
     * Shows data option page
     *
     * @return ViewModel|\Laminas\Http\Response
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
     */
    public function hasUninstallAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        if (isset($params['moduleName'])) {
            $uninstallClasses = $this->uninstallCollector->collectUninstall([$params['moduleName']]);
        }
        return new JsonModel(['hasUninstall' => isset($uninstallClasses) && count($uninstallClasses) > 0]);
    }
}
