<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

class SystemBackup
{
    const XML_PATH_BACKUP_ENABLED = 'system/backup/enabled';

    const XML_PATH_BACKUP_TYPE = 'system/backup/type';

    const XML_PATH_BACKUP_MAINTENANCE_MODE = 'system/backup/maintenance';

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Backup data
     *
     * @var \Magento\Backup\Helper\Data
     */
    protected $_backupData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Filesystem facade
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Backup\Factory
     */
    protected $_backupFactory;

    /**
     * @var \Magento\Framework\App\MaintenanceMode
     */
    protected $maintenanceMode;

    /**
     * @param \Magento\Backup\Helper\Data $backupData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Backup\Factory $backupFactory
     * @param \Magento\Framework\App\MaintenanceMode $maintenanceMode
     */
    public function __construct(
        \Magento\Backup\Helper\Data $backupData,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Backup\Factory $backupFactory,
        \Magento\Framework\App\MaintenanceMode $maintenanceMode
    ) {
        $this->_backupData = $backupData;
        $this->_coreRegistry = $coreRegistry;
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_filesystem = $filesystem;
        $this->_backupFactory = $backupFactory;
        $this->maintenanceMode = $maintenanceMode;
    }

    /**
     * Create Backup
     *
     * @return $this
     * @throws \Exception
     */
    public function execute()
    {
        if (!$this->_scopeConfig->isSetFlag(self::XML_PATH_BACKUP_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return $this;
        }

        if ($this->_scopeConfig->isSetFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE, ScopeInterface::SCOPE_STORE)) {
            $this->maintenanceMode->set(true);
        }

        $type = $this->_scopeConfig->getValue(self::XML_PATH_BACKUP_TYPE, ScopeInterface::SCOPE_STORE);

        $this->_errors = [];
        try {
            $backupManager = $this->_backupFactory->create(
                $type
            )->setBackupExtension(
                $this->_backupData->getExtensionByType($type)
            )->setTime(
                time()
            )->setBackupsDir(
                $this->_backupData->getBackupsDir()
            );

            $this->_coreRegistry->register('backup_manager', $backupManager);

            if ($type != \Magento\Framework\Backup\Factory::TYPE_DB) {
                $backupManager->setRootDir(
                    $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath()
                )->addIgnorePaths(
                    $this->_backupData->getBackupIgnorePaths()
                );
            }

            $backupManager->create();
            $message = $this->_backupData->getCreateSuccessMessageByType($type);
            $this->_logger->info($message);
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTrace();
            throw $e;
        }

        if ($this->_scopeConfig->isSetFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE, ScopeInterface::SCOPE_STORE)) {
            $this->maintenanceMode->set(false);
        }

        return $this;
    }
}
