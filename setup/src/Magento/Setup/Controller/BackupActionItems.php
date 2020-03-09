<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Laminas\Http\Response;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Backup\Filesystem;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Setup\BackupRollback;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\WebLogger;

/**
 * BackupActionItems controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackupActionItems extends AbstractActionController
{
    /**
     * Handler for BackupRollback
     *
     * @var BackupRollback
     */
    private $backupHandler;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * Filesystem Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param ObjectManagerProvider $objectManagerProvider
     * @param WebLogger $logger
     * @param DirectoryList $directoryList
     * @param Filesystem $fileSystem
     * @throws \Magento\Setup\Exception
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        WebLogger $logger,
        DirectoryList $directoryList,
        Filesystem $fileSystem
    ) {
        $objectManager = $objectManagerProvider->get();
        $this->backupHandler = $objectManager->create(
            BackupRollback::class,
            ['log' => $logger]
        );
        $this->directoryList = $directoryList;
        $this->fileSystem = $fileSystem;
    }

    /**
     * No index action, return 404 error page
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/error/404.phtml');
        $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);

        return $view;
    }

    /**
     * Checks disk space availability
     *
     * @return JsonModel
     * @throws FileSystemException
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
     * @return JsonModel
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
