<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface for work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Backup;

interface BackupInterface
{
    /**
     * Create Backup
     *
     * @return boolean
     */
    public function create();

    /**
     * Rollback Backup
     *
     * @return boolean
     */
    public function rollback();

    /**
     * Set Backup Extension
     *
     * @param string $backupExtension
     * @return $this
     */
    public function setBackupExtension($backupExtension);

    /**
     * Set Resource Model
     *
     * @param object $resourceModel
     * @return $this
     */
    public function setResourceModel($resourceModel);

    /**
     * Set Time
     *
     * @param int $time
     * @return $this
     */
    public function setTime($time);

    /**
     * Get Backup Type
     *
     * @return string
     */
    public function getType();

    /**
     * Set path to directory where backups stored
     *
     * @param string $backupsDir
     * @return $this
     */
    public function setBackupsDir($backupsDir);
}
