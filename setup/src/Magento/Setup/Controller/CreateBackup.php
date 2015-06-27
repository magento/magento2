<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Setup\BackupRollback;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\WebLogger;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class CreateBackup extends AbstractActionController
{
    /**
     * Handler for maintenance mode
     *
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * Factory for BackupRollback
     *
     * @var BackupRollback
     */
    private $backupHandler;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param MaintenanceMode $maintenanceMode
     * @param WebLogger $logger
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        MaintenanceMode $maintenanceMode,
        WebLogger $logger
    ) {
        $objectManager = $objectManagerProvider->get();
        $this->maintenanceMode = $maintenanceMode;
        $this->backupHandler = $objectManager->create('Magento\Framework\Setup\BackupRollback', ['log' => $logger]);
    }

    /**
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Takes backup for code, media or DB
     *
     * @return JsonModel
     */
    public function createAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        try {
            $this->maintenanceMode->set(true);
            $time = time();
            $backupFiles = [];
            if ($params['options']['code']) {
                $backupFiles[] = $this->backupHandler->codeBackup($time);
            }
            if ($params['options']['media']) {
                $backupFiles[] = $this->backupHandler->codeBackup($time, Factory::TYPE_MEDIA);
            }
            if ($params['options']['db']) {
                $backupFiles[] = $this->backupHandler->dbBackup($time);
            }
            return new JsonModel(['success' => true, 'backupFiles' => $backupFiles]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
