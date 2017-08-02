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
class BackupFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    private $_objectManager;

    /**
     * @var string
     * @since 2.0.0
     */
    private $_backupInstanceName;

    /**
     * @var string
     * @since 2.0.0
     */
    private $_backupDbInstanceName;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $backupInstanceName
     * @param string $backupDbInstanceName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $backupInstanceName,
        $backupDbInstanceName
    ) {
        $this->_objectManager = $objectManager;
        $this->_backupInstanceName = $backupInstanceName;
        $this->_backupDbInstanceName = $backupDbInstanceName;
    }

    /**
     * Create backup model
     *
     * @param array $arguments
     * @return \Magento\Framework\Backup\Db\BackupInterface
     * @since 2.0.0
     */
    public function createBackupModel(array $arguments = [])
    {
        return $this->_objectManager->create($this->_backupInstanceName, $arguments);
    }

    /**
     * Create backup Db model
     *
     * @param array $arguments
     * @return \Magento\Framework\Backup\Db\BackupDbInterface
     * @since 2.0.0
     */
    public function createBackupDbModel(array $arguments = [])
    {
        return $this->_objectManager->create($this->_backupDbInstanceName, $arguments);
    }
}
