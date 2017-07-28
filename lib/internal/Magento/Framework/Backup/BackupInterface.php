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

/**
 * @api
 * @since 2.0.0
 */
interface BackupInterface
{
    /**
     * Create Backup
     *
     * @return boolean
     * @since 2.0.0
     */
    public function create();

    /**
     * Rollback Backup
     *
     * @return boolean
     * @since 2.0.0
     */
    public function rollback();

    /**
     * Set Backup Extension
     *
     * @param string $backupExtension
     * @return $this
     * @since 2.0.0
     */
    public function setBackupExtension($backupExtension);

    /**
     * Set Resource Model
     *
     * @param object $resourceModel
     * @return $this
     * @since 2.0.0
     */
    public function setResourceModel($resourceModel);

    /**
     * Set Time
     *
     * @param int $time
     * @return $this
     * @since 2.0.0
     */
    public function setTime($time);

    /**
     * Get Backup Type
     *
     * @return string
     * @since 2.0.0
     */
    public function getType();

    /**
     * Set path to directory where backups stored
     *
     * @param string $backupsDir
     * @return $this
     * @since 2.0.0
     */
    public function setBackupsDir($backupsDir);
}
