<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Upgrade job
 */
class JobRollback extends AbstractJob
{
    /**
     * @var MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param MaintenanceMode $maintenanceMode
     * @param OutputInterface $output
     * @param Status $status
     * @param string $name
     * @param array $params
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        MaintenanceMode $maintenanceMode,
        OutputInterface $output,
        Status $status,
        $name,
        $params = []
    ) {
        $objectManager = $objectManagerProvider->get();
        $this->directoryList  = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $this->backupRollbackFactory = $objectManager->get('Magento\Framework\Setup\BackupRollbackFactory');
        $this->maintenanceMode = $maintenanceMode;
        parent::__construct($output, $status, $name, $params);
    }

    /**
     * Execute job
     *
     * @throws \RuntimeException
     * @return void
     */
    public function execute()
    {
        try {
            $rollbackHandler = $this->backupRollbackFactory->create($this->output);
            $rollbackHandler->dbRollback($this->getLastBackupFilePath("db"));
            $this->maintenanceMode->set(false);
        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
    }

    /**
     * Find the last backup file from backup directory.
     *
     * @param string $type
     * @throws \RuntimeException
     * @return string
     */
    protected function getLastBackupFilePath($type)
    {
        $backupsDir = $this->directoryList->getPath(DirectoryList::VAR_DIR)
            . '/' . BackupRollback::DEFAULT_BACKUP_DIRECTORY;

        $allFileList = scandir($backupsDir, SCANDIR_SORT_DESCENDING);
        $backupFileName = '';

        foreach ($allFileList as $fileName) {
            if (strpos($fileName, $type) !== false) {
                $backupFileName = $fileName;
                break;
            }
        }

        if (empty($backupFileName)) {
            throw new \RuntimeException("No available backup file found.");
        }
        return $backupFileName;
    }
}
