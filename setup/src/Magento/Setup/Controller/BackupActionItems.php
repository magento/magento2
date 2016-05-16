<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Backup\Filesystem;
use Magento\Framework\Setup\BackupRollback;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class BackupActionItems extends AbstractActionController
{

    /**
     * Handler for BackupRollback
     *
     * @var \Magento\Framework\Setup\BackupRollback
     */
    private $backupHandler;

    /**
     * Filesystem
     *
     * @var \Magento\Framework\Backup\Filesystem
     */
    private $fileSystem;

    /**
     * Filesystem Directory List
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * Constructor
     *
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param \Magento\Setup\Model\WebLogger $logger
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Backup\Filesystem $fileSystem
     */
    public function __construct(
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        \Magento\Setup\Model\WebLogger $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Backup\Filesystem $fileSystem
    ) {
        $objectManager = $objectManagerProvider->get();
        $this->backupHandler = $objectManager->create('Magento\Framework\Setup\BackupRollback', ['log' => $logger]);
        $this->directoryList = $directoryList;
        $this->fileSystem = $fileSystem;
    }

    /**
     * No index action, return 404 error page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view = new \Zend\View\Model\ViewModel;
        $view->setTemplate('/error/404.phtml');
        $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_404);
        return $view;
    }

    /**
     * Checks disk space availability
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function checkAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        $backupDir = $this->directoryList->getPath(DirectoryList::VAR_DIR)
            . '/' . BackupRollback::DEFAULT_BACKUP_DIRECTORY;
        try {
            $totalSize = 0;
            if (isset($params['options']['code']) && $params['options']['code']) {
                $totalSize += $this->backupHandler->getFSDiskSpace();
            }
            if (isset($params['options']['media']) && $params['options']['media']) {
                $totalSize += $this->backupHandler->getFSDiskSpace(Factory::TYPE_MEDIA);
            }
            if (isset($params['options']['db']) && $params['options']['db']) {
                $totalSize += $this->backupHandler->getDBDiskSpace();
            }
            $this->fileSystem->validateAvailableDiscSpace($backupDir, $totalSize);
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

    /**
     * Takes backup for code, media or DB
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function createAction()
    {
        $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        try {
            $time = time();
            $backupFiles = [];
            if (isset($params['options']['code']) && $params['options']['code']) {
                $backupFiles[] = $this->backupHandler->codeBackup($time);
            }
            if (isset($params['options']['media']) && $params['options']['media']) {
                $backupFiles[] = $this->backupHandler->codeBackup($time, Factory::TYPE_MEDIA);
            }
            if (isset($params['options']['db']) && $params['options']['db']) {
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
}
