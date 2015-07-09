<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Setup\BackupRollback;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\WebLogger;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class BackupChecks extends AbstractActionController
{

    /**
     * Factory for BackupRollback
     *
     * @var BackupRollback
     */
    private $backupHandler;

    /**
     * Filesystem Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param WebLogger $logger
     * @param DirectoryList $directoryList
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        WebLogger $logger,
        DirectoryList $directoryList
    ) {
        $objectManager = $objectManagerProvider->get();
        $this->backupHandler = $objectManager->create('Magento\Framework\Setup\BackupRollback', ['log' => $logger]);
        $this->directoryList = $directoryList;
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
            return new JsonModel(
                [
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
                    'files' => $backupFiles
                ]
            );
        } catch (\Exception $e) {
            return new JsonModel(
                [
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Checks disk space availability
     *
     * @return JsonModel
     */
    public function checkAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $backupDir = $this->directoryList->getPath(DirectoryList::VAR_DIR)
            . '/' . BackupRollback::DEFAULT_BACKUP_DIRECTORY;
        try {
            $totalSize = 0;
            if ($params['options']['code']) {
                $totalSize += $this->backupHandler->getFSDiskSpace();
            }
            if ($params['options']['media']) {
                $totalSize += $this->backupHandler->getFSDiskSpace(Factory::TYPE_MEDIA);
            }
            if ($params['options']['db']) {
                $totalSize += $this->backupHandler->getDBDiskSpace();
            }
            $freeSpace = disk_free_space($backupDir);
            if (2 * $totalSize > $freeSpace) {
                throw new \Magento\Framework\Backup\Exception\NotEnoughFreeSpace(
                    'Not enough free space to create backup'
                );
            }
            return new JsonModel(
                [
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_SUCCESS,
                    'size' => true
                ]
            );
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
