<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Db;

interface BackupDbInterface
{
    /**
     * Create DB backup
     *
     * @param BackupInterface $backup
     * @return void
     */
    public function createBackup(\Magento\Framework\Backup\Db\BackupInterface $backup);
}
