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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup data helper
 */
namespace Magento\Backup\Helper;

class Data extends \Magento\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * Index resource process collection factory
     *
     * @var \Magento\Index\Model\Resource\Process\CollectionFactory
     */
    protected $_processFactory;

    /**
     * Construct
     *
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\AuthorizationInterface $authorization
     * @param \Magento\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Index\Model\Resource\Process\CollectionFactory $processFactory
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Filesystem $filesystem,
        \Magento\AuthorizationInterface $authorization,
        \Magento\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Index\Model\Resource\Process\CollectionFactory $processFactory
    ) {
        parent::__construct($context);
        $this->_authorization = $authorization;
        $this->_filesystem = $filesystem;        
        $this->_cacheTypeList = $cacheTypeList;
    }

    /**
     * Get all possible backup type values with descriptive title
     *
     * @return array
     */
    public function getBackupTypes()
    {
        return array(
            \Magento\Backup\Factory::TYPE_DB                     => __('Database'),
            \Magento\Backup\Factory::TYPE_MEDIA                  => __('Database and Media'),
            \Magento\Backup\Factory::TYPE_SYSTEM_SNAPSHOT        => __('System'),
            \Magento\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __('System (excluding Media)')
        );
    }

    /**
     * Get all possible backup type values
     *
     * @return array
     */
    public function getBackupTypesList()
    {
        return array(
            \Magento\Backup\Factory::TYPE_DB,
            \Magento\Backup\Factory::TYPE_SYSTEM_SNAPSHOT,
            \Magento\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA,
            \Magento\Backup\Factory::TYPE_MEDIA
        );
    }

    /**
     * Get default backup type value
     *
     * @return string
     */
    public function getDefaultBackupType()
    {
        return \Magento\Backup\Factory::TYPE_DB;
    }

    /**
     * Get directory path where backups stored
     *
     * @return string
     */
    public function getBackupsDir()
    {
        return $this->_filesystem->getPath(\Magento\Filesystem::VAR_DIR) . '/backups';
    }

    /**
     * Get backup file extension by backup type
     *
     * @param string $type
     * @return string
     */
    public function getExtensionByType($type)
    {
        $extensions = $this->getExtensions();
        return isset($extensions[$type]) ? $extensions[$type] : '';
    }

    /**
     * Get all types to extensions map
     *
     * @return array
     */
    public function getExtensions()
    {
        return array(
            \Magento\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => 'tgz',
            \Magento\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => 'tgz',
            \Magento\Backup\Factory::TYPE_MEDIA => 'tgz',
            \Magento\Backup\Factory::TYPE_DB => 'gz'
        );
    }

    /**
     * Generate backup download name
     *
     * @param \Magento\Backup\Model\Backup $backup
     * @return string
     */
    public function generateBackupDownloadName(\Magento\Backup\Model\Backup $backup)
    {
        $additionalExtension = $backup->getType() == \Magento\Backup\Factory::TYPE_DB ? '.sql' : '';
        return $backup->getType() . '-' . date('YmdHis', $backup->getTime()) . $additionalExtension . '.'
            . $this->getExtensionByType($backup->getType());
    }

    /**
     * Check Permission for Rollback
     *
     * @return boolean
     */
    public function isRollbackAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Backup::rollback' );
    }

    /**
     * Get paths that should be ignored when creating system snapshots
     *
     * @return array
     */
    public function getBackupIgnorePaths()
    {
        return array(
            '.git',
            '.svn',
            'maintenance.flag',
            $this->_filesystem->getPath(\Magento\Filesystem::SESSION),
            $this->_filesystem->getPath(\Magento\Filesystem::CACHE),
            $this->_filesystem->getPath(\Magento\Filesystem::LOG),
            $this->_filesystem->getPath(\Magento\Filesystem::VAR_DIR) . '/full_page_cache',
            $this->_filesystem->getPath(\Magento\Filesystem::VAR_DIR) . '/locks',
            $this->_filesystem->getPath(\Magento\Filesystem::VAR_DIR) . '/report',
        );
    }

    /**
     * Get paths that should be ignored when rolling back system snapshots
     *
     * @return array
     */
    public function getRollbackIgnorePaths()
    {
        return array(
            '.svn',
            '.git',
            'maintenance.flag',
            $this->_filesystem->getPath(\Magento\Filesystem::SESSION),
            $this->_filesystem->getPath(\Magento\Filesystem::LOG),
            $this->_filesystem->getPath(\Magento\Filesystem::VAR_DIR) . '/locks',
            $this->_filesystem->getPath(\Magento\Filesystem::VAR_DIR) . '/report',
            $this->_filesystem->getPath(\Magento\Filesystem::ROOT) . '/errors',
            $this->_filesystem->getPath(\Magento\Filesystem::ROOT) . '/index.php',
        );
    }

    /**
     * Put store into maintenance mode
     *
     * @return bool
     */
    public function turnOnMaintenanceMode()
    {
        $maintenanceFlagFile = $this->getMaintenanceFlagFilePath();
        $result = $this->_filesystem
            ->getDirectoryWrite(\Magento\Filesystem::ROOT)
            ->writeFile($maintenanceFlagFile, 'maintenance');

        return $result !== false;
    }

    /**
     * Turn off store maintenance mode
     */
    public function turnOffMaintenanceMode()
    {
        $maintenanceFlagFile = $this->getMaintenanceFlagFilePath();
        $this->_filesystem->getDirectoryWrite(\Magento\Filesystem::ROOT)->delete($maintenanceFlagFile);
    }

    /**
     * Get backup create success message by backup type
     *
     * @param string $type
     * @return string
     */
    public function getCreateSuccessMessageByType($type)
    {
        $messagesMap = array(
            \Magento\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => __('The system backup has been created.'),
            \Magento\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __('The system backup (excluding media) has been created.'),
            \Magento\Backup\Factory::TYPE_MEDIA => __('The database and media backup has been created.'),
            \Magento\Backup\Factory::TYPE_DB => __('The database backup has been created.')
        );

        if (!isset($messagesMap[$type])) {
            return;
        }

        return $messagesMap[$type];
    }

    /**
     * Get path to maintenance flag file
     *
     * @return string
     */
    protected function getMaintenanceFlagFilePath()
    {
        return 'maintenance.flag';
    }

    /**
     * Invalidate Cache
     *
     * @return \Magento\Backup\Helper\Data
     */
    public function invalidateCache()
    {
        if ($cacheTypes = $this->_cacheConfig->getTypes()) {
            $cacheTypesList = array_keys($cacheTypes);
            $this->_cacheTypeList->invalidate($cacheTypesList);
        }
        return $this;
    }

    /**
     * Invalidate Indexer
     *
     * @return \Magento\Backup\Helper\Data
     */
    public function invalidateIndexer()
    {
        foreach ($this->_processFactory->create() as $process) {
            $process->changeStatus(\Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX);
        }
        return $this;
    }

    /**
     * Creates backup's display name from it's name
     *
     * @param string $name
     * @return string
     */
    public function nameToDisplayName($name)
    {
        return str_replace('_', ' ', $name);
    }

    /**
     * Extracts information from backup's filename
     *
     * @param string $filename
     * @return \Magento\Object
     */
    public function extractDataFromFilename($filename)
    {
        $extensions = $this->getExtensions();

        $filenameWithoutExtension = $filename;

        foreach ($extensions as $extension) {
            $filenameWithoutExtension = preg_replace('/' . preg_quote($extension, '/') . '$/', '',
                $filenameWithoutExtension
            );
        }

        $filenameWithoutExtension = substr($filenameWithoutExtension, 0, strrpos($filenameWithoutExtension, "."));

        list($time, $type) = explode("_", $filenameWithoutExtension);

        $name = str_replace($time . '_' . $type, '', $filenameWithoutExtension);

        if (!empty($name)) {
            $name = substr($name, 1);
        }

        $result = new \Magento\Object();
        $result->addData(array(
            'name' => $name,
            'type' => $type,
            'time' => $time
        ));

        return $result;
    }
}
