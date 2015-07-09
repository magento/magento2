<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\MaintenanceMode;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class CompleteBackup extends AbstractActionController
{
    /**
     * Handler for maintenance mode
     *
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
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/complete-backup.phtml');
        return $view;
    }

    /**
     * @return array|ViewModel
     */
    public function progressAction()
    {
        $view = new ViewModel;
        $view->setTemplate('/magento/setup/complete-backup/progress.phtml');
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Puts store in maintenance mode
     *
     * @return JsonModel
     */
    public function maintenanceAction()
    {
        try {
            $this->maintenanceMode->set(true);
            return new JsonModel(['responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS]);
        } catch (\Exception $e) {
            return new JsonModel(
                [
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                    'error' => $e->getMessage()
                ]
            );
        }
    }
}
