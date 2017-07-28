<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\MaintenanceMode;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class \Magento\Setup\Controller\UpdaterSuccess
 *
 * @since 2.0.0
 */
class UpdaterSuccess extends AbstractActionController
{
    /**
     * @var MaintenanceMode
     * @since 2.0.0
     */
    private $maintenanceMode;

    /**
     * Constructor
     *
     * @param MaintenanceMode $maintenanceMode
     * @since 2.0.0
     */
    public function __construct(MaintenanceMode $maintenanceMode)
    {
        $this->maintenanceMode = $maintenanceMode;
    }

    /**
     * @return ViewModel
     * @since 2.0.0
     */
    public function indexAction()
    {
        $this->maintenanceMode->set(false);
        $view = new ViewModel;
        $view->setTerminal(true);
        return $view;
    }
}
