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
namespace Magento\Backup\Helper;

use Magento\Framework\App\MaintenanceMode;

/**
 * Backup data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Filesystem
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
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem $filesystem,
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
        return array(
            \Magento\Framework\Backup\Factory::TYPE_DB => __('Database'),
            \Magento\Framework\Backup\Factory::TYPE_MEDIA => __('Database and Media'),
            \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => __('System'),
            \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __('System (excluding Media)')
        );
    }

    /**
     * Get all possible backup type values
     *
     * @return string[]
     */
    public function getBackupTypesList()
    {
        return array(
            \Magento\Framework\Backup\Factory::TYPE_DB,
            \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT,
            \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA,
            \Magento\Framework\Backup\Factory::TYPE_MEDIA
        );
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
        return $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::VAR_DIR) . '/backups';
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
            \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => 'tgz',
            \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => 'tgz',
            \Magento\Framework\Backup\Factory::TYPE_MEDIA => 'tgz',
            \Magento\Framework\Backup\Factory::TYPE_DB => 'gz'
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
        return array(
            '.git',
            '.svn',
            $this->_filesystem->getPath(MaintenanceMode::FLAG_DIR) . '/' . MaintenanceMode::FLAG_FILENAME,
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::SESSION_DIR),
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::CACHE_DIR),
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::LOG_DIR),
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::VAR_DIR) . '/full_page_cache',
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::VAR_DIR) . '/locks',
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::VAR_DIR) . '/report'
        );
    }

    /**
     * Get paths that should be ignored when rolling back system snapshots
     *
     * @return string[]
     */
    public function getRollbackIgnorePaths()
    {
        return array(
            '.svn',
            '.git',
            $this->_filesystem->getPath(MaintenanceMode::FLAG_DIR) . '/' . MaintenanceMode::FLAG_FILENAME,
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::SESSION_DIR),
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::LOG_DIR),
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::VAR_DIR) . '/locks',
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::VAR_DIR) . '/report',
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::ROOT_DIR) . '/errors',
            $this->_filesystem->getPath(\Magento\Framework\App\Filesystem::ROOT_DIR) . '/index.php'
        );
    }

    /**
     * Get backup create success message by backup type
     *
     * @param string $type
     * @return void|string
     */
    public function getCreateSuccessMessageByType($type)
    {
        $messagesMap = array(
            \Magento\Framework\Backup\Factory::TYPE_SYSTEM_SNAPSHOT => __('The system backup has been created.'),
            \Magento\Framework\Backup\Factory::TYPE_SNAPSHOT_WITHOUT_MEDIA => __(
                'The system backup (excluding media) has been created.'
            ),
            \Magento\Framework\Backup\Factory::TYPE_MEDIA => __('The database and media backup has been created.'),
            \Magento\Framework\Backup\Factory::TYPE_DB => __('The database backup has been created.')
        );

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
     * @return \Magento\Framework\Object
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

        $result = new \Magento\Framework\Object();
        $result->addData(array('name' => $name, 'type' => $type, 'time' => $time));

        return $result;
    }
}
