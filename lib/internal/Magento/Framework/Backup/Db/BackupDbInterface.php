<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Db;

/**
 * @api
 * @since 2.0.0
 */
interface BackupDbInterface
{
    /**
     * Create DB backup
     *
     * @param BackupInterface $backup
     * @return void
     * @since 2.0.0
     */
    public function createBackup(\Magento\Framework\Backup\Db\BackupInterface $backup);

    /**
     * Get database backup size
     *
     * @return int
     * @since 2.0.0
     */
    public function getDBBackupSize();
}
