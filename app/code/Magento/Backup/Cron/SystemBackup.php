<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

/**
 * Class \Magento\Backup\Cron\SystemBackup
 *
 * @since 2.0.0
 */
class SystemBackup
{
    const XML_PATH_BACKUP_ENABLED = 'system/backup/enabled';

    const XML_PATH_BACKUP_TYPE = 'system/backup/type';

    const XML_PATH_BACKUP_MAINTENANCE_MODE = 'system/backup/maintenance';

    /**
     * Error messages
     *
     * @var array
     * @since 2.0.0
     */
    protected $_errors = [];

    /**
     * Backup data
     *
     * @var \Magento\Backup\Helper\Data
     * @since 2.0.0
     */
    protected $_backupData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * Filesystem facade
     *
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Backup\Factory
     * @since 2.0.0
     */
    protected $_backupFactory;

    /**
     * @var \Magento\Framework\App\MaintenanceMode
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
