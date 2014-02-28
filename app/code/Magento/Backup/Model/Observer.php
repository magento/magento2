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
 * @category    Magento
 * @package     Magento_Backup
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup Observer
 *
 * @category   Magento
 * @package    Magento_Backup
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup\Model;

class Observer
{
    const XML_PATH_BACKUP_ENABLED          = 'system/backup/enabled';
    const XML_PATH_BACKUP_TYPE             = 'system/backup/type';
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
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Filesystem facade
     *
     * @var \Magento\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Backup\Factory
     */
    protected $_backupFactory;

    /**
     * @param \Magento\Backup\Helper\Data $backupData
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\Logger $logger
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Backup\Factory $backupFactory
     */
    public function __construct(
        \Magento\Backup\Helper\Data $backupData,
        \Magento\Registry $coreRegistry,
        \Magento\Logger $logger,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\App\Filesystem $filesystem,
        \Magento\Backup\Factory $backupFactory
    ) {
        $this->_backupData = $backupData;
        $this->_coreRegistry = $coreRegistry;
        $this->_logger = $logger;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_filesystem = $filesystem;
        $this->_backupFactory = $backupFactory;
    }

    /**
     * Create Backup
     *
     * @return \Magento\Log\Model\Cron
     */
    public function scheduledBackup()
    {
        if (!$this->_coreStoreConfig->getConfigFlag(self::XML_PATH_BACKUP_ENABLED)) {
            return $this;
        }

        if ($this->_coreStoreConfig->getConfigFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE)) {
            $this->_backupData->turnOnMaintenanceMode();
        }

        $type = $this->_coreStoreConfig->getConfig(self::XML_PATH_BACKUP_TYPE);

        $this->_errors = array();
        try {
            $backupManager = $this->_backupFactory->create($type)
                ->setBackupExtension($this->_backupData->getExtensionByType($type))
                ->setTime(time())
                ->setBackupsDir($this->_backupData->getBackupsDir());

            $this->_coreRegistry->register('backup_manager', $backupManager);

            if ($type != \Magento\Backup\Factory::TYPE_DB) {
                $backupManager->setRootDir($this->_filesystem->getPath(\Magento\App\Filesystem::ROOT_DIR))
                    ->addIgnorePaths($this->_backupData->getBackupIgnorePaths());
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

        if ($this->_coreStoreConfig->getConfigFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE)) {
            $this->_backupData->turnOffMaintenanceMode();
        }

        return $this;
    }
}
