<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backup\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\Filesystem;

/**
 * Backup data helper
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Filesystem $filesystem
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Filesystem $filesystem,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
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
        return [
            \Magento\Framework\Backup\Factory::TYPE_DB => __('Database'),
            \Magento\Framework\Backup\Factory::TYPE_MEDIA => __('Database and Media'),
            \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => __('System'),
            \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __('System (excluding Media)')
        ];
    }

    /**
     * Get all possible backup type values
     *
     * @return string[]
     */
    public function getBackupTypesList()
    {
        return [
            \Magento\Framework\Backup\Factory::TYPE_DB,
            \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT,
            \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA,
            \Magento\Framework\Backup\Factory::TYPE_MEDIA
        ];
    }

    /**
     * Get default backup type value
     *
     * @return string
     */
    public function getDefaultBackupType()
    {
        return \Magento\Framework\Backup\Factory::TYPE_DB;
    }

    /**
     * Get directory path where backups stored
     *
     * @return string
     */
    public function getBackupsDir()
    {
        return $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->getAbsolutePath('backups');
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
        return $extensions[$type] ?? '';
    }

    /**
     * Get all types to extensions map
     *
     * @return array
     */
    public function getExtensions()
    {
        return [
            \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => 'tgz',
            \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => 'tgz',
            \Magento\Framework\Backup\Factory::TYPE_MEDIA => 'tgz',
            \Magento\Framework\Backup\Factory::TYPE_DB => 'sql'
        ];
    }

    /**
     * Generate backup download name
     *
     * @param \Magento\Backup\Model\Backup $backup
     * @return string
     */
    public function generateBackupDownloadName(\Magento\Backup\Model\Backup $backup)
    {
        $additionalExtension = $backup->getType() == \Magento\Framework\Backup\Factory::TYPE_DB ? '.sql' : '';
        return $backup->getTime() .
            '_' .
            $backup->getType() .
            '_' .
            $backup->getName() .
            $additionalExtension .
            '.' .
            $this->getExtensionByType(
                $backup->getType()
            );
    }

    /**
     * Check Permission for Rollback
     *
     * @return bool
     */
    public function isRollbackAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Backup::rollback');
    }

    /**
     * Get paths that should be ignored when creating system snapshots
     *
     * @return string[]
     */
    public function getBackupIgnorePaths()
    {
        return [
            '.git',
            '.svn',
            $this->_filesystem->getDirectoryRead(MaintenanceMode::FLAG_DIR)
                ->getAbsolutePath(MaintenanceMode::FLAG_FILENAME),
            $this->_filesystem->getDirectoryRead(DirectoryList::SESSION)->getAbsolutePath(),
            $this->_filesystem->getDirectoryRead(DirectoryList::CACHE)->getAbsolutePath(),
            $this->_filesystem->getDirectoryRead(DirectoryList::LOG)->getAbsolutePath(),
            $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('full_page_cache'),
            $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('locks'),
            $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('report'),
        ];
    }

    /**
     * Get paths that should be ignored when rolling back system snapshots
     *
     * @return string[]
     */
    public function getRollbackIgnorePaths()
    {
        return [
            '.svn',
            '.git',
            $this->_filesystem->getDirectoryRead(MaintenanceMode::FLAG_DIR)
                ->getAbsolutePath(MaintenanceMode::FLAG_FILENAME),
            $this->_filesystem->getDirectoryRead(DirectoryList::SESSION)->getAbsolutePath(),
            $this->_filesystem->getDirectoryRead(DirectoryList::LOG)->getAbsolutePath(),
            $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('locks'),
            $this->_filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath('report'),
            $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('errors'),
            $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath('index.php'),
        ];
    }

    /**
     * Get backup create success message by backup type
     *
     * @param string $type
     * @return void|string
     */
    public function getCreateSuccessMessageByType($type)
    {
        $messagesMap = [
            \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => __('You created the system backup.'),
            \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __(
                'You created the system backup (excluding media).'
            ),
            \Magento\Framework\Backup\Factory::TYPE_MEDIA => __('You created the database and media backup.'),
            \Magento\Framework\Backup\Factory::TYPE_DB => __('You created the database backup.'),
        ];

        if (!isset($messagesMap[$type])) {
            return;
        }

        return $messagesMap[$type];
    }

    /**
     * Invalidate Cache
     *
     * @return $this
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
     * @return \Magento\Framework\DataObject
     */
    public function extractDataFromFilename($filename)
    {
        $extensions = $this->getExtensions();

        $filenameWithoutExtension = $filename;

        foreach ($extensions as $extension) {
            $filenameWithoutExtension = preg_replace(
                '/' . preg_quote($extension, '/') . '$/',
                '',
                $filenameWithoutExtension
            );
        }

        $filenameWithoutExtension = substr($filenameWithoutExtension, 0, strrpos($filenameWithoutExtension, "."));

        list($time, $type) = explode("_", $filenameWithoutExtension);

        $name = str_replace($time . '_' . $type, '', $filenameWithoutExtension);

        if (!empty($name)) {
            $name = substr($name, 1);
        }

        $result = new \Magento\Framework\DataObject();
        $result->addData(['name' => $name, 'type' => $type, 'time' => $time]);

        return $result;
    }

    /**
     * Is backup functionality enabled.
     *
     * @return bool
     * @since 100.2.6
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('system/backup/functionality_enabled');
    }
}
