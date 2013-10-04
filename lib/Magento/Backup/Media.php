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
 * @package     \Magento\Backup
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work media folder and database backups
 *
 * @category    Magento
 * @package     \Magento\Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backup;

class Media extends \Magento\Backup\AbstractBackup
{
    /**
     * Snapshot backup manager instance
     *
     * @var \Magento\Backup\Snapshot
     */
    protected $_snapshotManager;

    /**
     * @param \Magento\Backup\Snapshot $snapshotManager
     */
    public function __construct(
        \Magento\Backup\Snapshot $snapshotManager
    ) {
        $this->_snapshotManager = $snapshotManager;
    }

    /**
     * Implementation Rollback functionality for Snapshot
     *
     * @throws \Magento\Exception
     * @return bool
     */
    public function rollback()
    {
        $this->_prepareIgnoreList();
        return $this->_snapshotManager->rollback();
    }

    /**
     * Implementation Create Backup functionality for Snapshot
     *
     * @throws \Magento\Exception
     * @return bool
     */
    public function create()
    {
        $this->_prepareIgnoreList();
        return $this->_snapshotManager->create();
    }

    /**
     * Overlap getType
     *
     * @return string
     * @see \Magento\Backup\BackupInterface::getType()
     */
    public function getType()
    {
        return 'media';
    }

    /**
     * Add all folders and files except media and db backup to ignore list
     *
     * @return \Magento\Backup\Media
     */
    protected function _prepareIgnoreList()
    {
        $rootDir = $this->_snapshotManager->getRootDir();
        $map = array(
            $rootDir => array('media', 'var', 'pub'),
            $rootDir . DIRECTORY_SEPARATOR . 'pub' => array('media'),
            $rootDir . DIRECTORY_SEPARATOR . 'var' => array($this->_snapshotManager->getDbBackupFilename()),
        );

        foreach ($map as $path => $whiteList) {
            foreach (new \DirectoryIterator($path) as $item) {
                $filename = $item->getFilename();
                if (!$item->isDot() && !in_array($filename, $whiteList)) {
                    $this->_snapshotManager->addIgnorePaths($item->getPathname());
                }
            }
        }

        return $this;
    }

    /**
     * Set Backup Extension
     *
     * @param string $backupExtension
     * @return \Magento\Backup\BackupInterface
     */
    public function setBackupExtension($backupExtension)
    {
        $this->_snapshotManager->setBackupExtension($backupExtension);
        return $this;
    }

    /**
     * Set Resource Model
     *
     * @param object $resourceModel
     * @return \Magento\Backup\BackupInterface
     */
    public function setResourceModel($resourceModel)
    {
        $this->_snapshotManager->setResourceModel($resourceModel);
        return $this;
    }

    /**
     * Set Time
     *
     * @param int $time
     * @return \Magento\Backup\BackupInterface
     */
    public function setTime($time)
    {
        $this->_snapshotManager->setTime($time);
        return $this;
    }

    /**
     * Set path to directory where backups stored
     *
     * @param string $backupsDir
     * @return \Magento\Backup\BackupInterface
     */
    public function setBackupsDir($backupsDir)
    {
        $this->_snapshotManager->setBackupsDir($backupsDir);
        return $this;
    }

    /**
     * Add path that should be ignoring when creating or rolling back backup
     *
     * @param string|array $paths
     * @return \Magento\Backup\BackupInterface
     */
    public function addIgnorePaths($paths)
    {
        $this->_snapshotManager->addIgnorePaths($paths);
        return $this;
    }

    /**
     * Set root directory of Magento installation
     *
     * @param string $rootDir
     * @throws \Magento\Exception
     * @return \Magento\Backup\BackupInterface
     */
    public function setRootDir($rootDir)
    {
        $this->_snapshotManager->setRootDir($rootDir);
        return $this;
    }
}
