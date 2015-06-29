<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Framework\Setup\BackupRollback;
use Magento\Framework\Setup\BackupRollbackFactory;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Upgrade job
 */
class JobDbRollback extends AbstractJob
{
    /**
     * @var BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * Constructor
     *
     * @param BackupRollbackFactory $backupRollbackFactory
     * @param OutputInterface $output
     * @param Status $status
     * @param string $name
     * @param array $params
     */
    public function __construct(
        BackupRollbackFactory $backupRollbackFactory,
        OutputInterface $output,
        Status $status,
        $name,
        $params = []
    ) {
        $this->backupRollbackFactory = $backupRollbackFactory;
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
            $dbBackupFile = $this->params['backup_file_name'];
            if (!empty($dbBackupFile)) {
                $rollbackHandler->dbRollback(basename($dbBackupFile));
            } else {
                $this->status->add(
                    'No available DB backup file found. Please refer to documentation specified '
                    . 'in <a href=""> doc link </a> to rollback database to a previous version to '
                );
            }
        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(
                sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
