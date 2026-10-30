<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup\Db;

/**
 * @api
 *
 * @deprecated 101.0.7 Backups should be done using other means.
 * @since 100.0.2
 */
interface BackupDbInterface
{
    /**
     * Create DB backup
     *
     * @param BackupInterface $backup
     * @return void
     */
    public function createBackup(\Magento\Framework\Backup\Db\BackupInterface $backup);

    /**
     * Get database backup size
     *
     * @return int
     */
    public function getDBBackupSize();
}
