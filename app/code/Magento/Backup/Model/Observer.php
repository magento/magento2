<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup Observer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup\Model;

use Magento\Store\Model\ScopeInterface;

class Observer
{
    const XML_PATH_BACKUP_ENABLED = 'system/backup/enabled';

    const XML_PATH_BACKUP_TYPE = 'system/backup/type';

    const XML_PATH_BACKUP_MAINTENANCE_MODE = 'system/backup/maintenance';

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = array();

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
     * @var \Magento\Framework\Logger
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
     * @var \Magento\Framework\App\Filesystem
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
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\Backup\Factory $backupFactory
     * @param \Magento\Framework\App\MaintenanceMode $maintenanceMode
     */
    public function __construct(
        \Magento\Backup\Helper\Data $backupData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Filesystem $filesystem,
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
     */
    public function scheduledBackup()
    {
        if (!$this->_scopeConfig->isSetFlag(self::XML_PATH_BACKUP_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return $this;
        }

        if ($this->_scopeConfig->isSetFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE, ScopeInterface::SCOPE_STORE)) {
            $this->maintenanceMode->set(true);
        }

        $type = $this->_scopeConfig->getValue(self::XML_PATH_BACKUP_TYPE, ScopeInterface::SCOPE_STORE);

        $this->_errors = array();
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
                    $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::ROOT_DIR)
                )->addIgnorePaths(
                    $this->_backupData->getBackupIgnorePaths()
                );
            }

            $backupManager->create();
            $message = $this->_backupData->getCreateSuccessMessageByType($type);
            $this->_logger->log($message);
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTrace();
            $this->_logger->log($e->getMessage(), \Zend_Log::ERR);
            $this->_logger->logException($e);
        }

        if ($this->_scopeConfig->isSetFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE, ScopeInterface::SCOPE_STORE)) {
            $this->maintenanceMode->set(false);
        }

        return $this;
    }
}
