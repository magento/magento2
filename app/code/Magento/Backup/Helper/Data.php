<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backup\Helper;

use Magento\Backup\Model\Backup;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\MaintenanceMode;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Filesystem;

/**
 * Backup data helper
 * @api
 * @since 100.0.2
 */
class Data extends AbstractHelper
{
    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * Construct
     *
     * @param Context $context
     * @param Filesystem $filesystem
     * @param AuthorizationInterface $authorization
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        AuthorizationInterface $authorization,
        TypeListInterface $cacheTypeList
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
            Factory::TYPE_DB => __('Database'),
            Factory::TYPE_MEDIA => __('Database and Media'),
            Factory::TYPE_SYSTEM_SNAPSHOT => __('System'),
            Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __('System (excluding Media)')
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
            Factory::TYPE_DB,
            Factory::TYPE_SYSTEM_SNAPSHOT,
            Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA,
            Factory::TYPE_MEDIA
        ];
    }

    /**
     * Get default backup type value
     *
     * @return string
     */
    public function getDefaultBackupType()
    {
        return Factory::TYPE_DB;
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
            Factory::TYPE_SYSTEM_SNAPSHOT => 'tgz',
            Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => 'tgz',
            Factory::TYPE_MEDIA => 'tgz',
            Factory::TYPE_DB => 'sql'
        ];
    }

    /**
     * Generate backup download name
     *
     * @param Backup $backup
     * @return string
     */
    public function generateBackupDownloadName(Backup $backup)
    {
        $additionalExtension = $backup->getType() == Factory::TYPE_DB ? '.sql' : '';
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
            Factory::TYPE_SYSTEM_SNAPSHOT => __('You created the system backup.'),
            Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __(
                'You created the system backup (excluding media).'
            ),
            Factory::TYPE_MEDIA => __('You created the database and media backup.'),
            Factory::TYPE_DB => __('You created the database backup.'),
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

        $filenameWithoutExtension = $filename ?: '';

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
