<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\MaintenanceMode;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UpdaterSuccess extends AbstractActionController
{
    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     */
    public function __construct(MaintenanceMode $maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->maintenanceMode->set(false);
        $view = new ViewModel;
        $view->setTerminal(true);
        return $view;
    }
}
