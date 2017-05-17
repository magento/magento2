<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Backup\Db;

class BackupFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var string
     */
    private $_backupInstanceName;

    /**
     * @var string
     */
    private $_backupDbInstanceName;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $backupInstanceName
     * @param string $backupDbInstanceName
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
     */
    public function createBackupDbModel(array $arguments = [])
    {
        return $this->_objectManager->create($this->_backupDbInstanceName, $arguments);
    }
}
