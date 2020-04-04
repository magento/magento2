<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Magento\Framework\App\MaintenanceMode;

/**
 * UpdaterSuccess controller
 */
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
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $this->maintenanceMode->set(false);
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }
}
