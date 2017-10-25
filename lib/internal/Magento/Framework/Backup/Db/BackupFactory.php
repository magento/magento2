<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Backup\Db;

use Magento\Framework\ObjectManagerInterface;

class BackupFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $backupInstanceName;

    /**
     * @var string
     */
    private $backupDbInstanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $backupInstanceName
     * @param string $backupDbInstanceName
     */
    public function __construct(ObjectManagerInterface $objectManager, $backupInstanceName, $backupDbInstanceName)
    {
        $this->objectManager = $objectManager;
        $this->backupInstanceName = $backupInstanceName;
        $this->backupDbInstanceName = $backupDbInstanceName;
    }

    /**
     * Create backup model
     *
     * @param array $arguments
     * @return \Magento\Framework\Backup\Db\BackupInterface
     */
    public function createBackupModel(array $arguments = [])
    {
        return $this->objectManager->create($this->backupInstanceName, $arguments);
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
