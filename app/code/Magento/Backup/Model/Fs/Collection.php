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
 * Backup data collection
 */
namespace Magento\Backup\Model\Fs;

class Collection extends \Magento\Data\Collection\Filesystem
{
    /**
     * Folder, where all backups are stored
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * Backup data
     *
     * @var \Magento\Backup\Helper\Data
     */
    protected $_backupData = null;

    /**
     * Directory model
     *
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * Backup model
     *
     * @var \Magento\Backup\Model\Backup
     */
    protected $_backup = null;

    /**
     * @param \Magento\Backup\Helper\Data $backupData
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\App\Dir $dir
     * @param \Magento\Backup\Model\Backup $backup
     */
    public function __construct(
        \Magento\Backup\Helper\Data $backupData,
        \Magento\Filesystem $filesystem,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\App\Dir $dir,
        \Magento\Backup\Model\Backup $backup
    ) {
        $this->_backupData = $backupData;
        parent::__construct($entityFactory);

        $this->_backupData = $backupData;
        $this->_filesystem = $filesystem;
        $this->_dir = $dir;
        $this->_backup = $backup;
        $this->_baseDir = $this->_dir->getDir(\Magento\App\Dir::VAR_DIR) . DS . 'backups';

        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->ensureDirectoryExists($this->_baseDir);
        $this->_filesystem->setWorkingDirectory($this->_baseDir);
        $this->_hideBackupsForApache();

        // set collection specific params
        $extensions = $this->_backupData->getExtensions();

        foreach ($extensions as $value) {
            $extensions[] = '(' . preg_quote($value, '/') . ')';
        }
        $extensions = implode('|', $extensions);

        $this->setOrder('time', self::SORT_ORDER_DESC)
            ->addTargetDir($this->_baseDir)
            ->setFilesFilter('/^[a-z0-9\-\_]+\.' . $extensions . '$/')
            ->setCollectRecursively(false);
    }

    /**
     * Create .htaccess file and deny backups directory access from web
     */
    protected function _hideBackupsForApache()
    {
        $htaccessPath = $this->_baseDir . DS . '.htaccess';
        if (!$this->_filesystem->isFile($htaccessPath)) {
            $this->_filesystem->write($htaccessPath, 'deny from all');
            $this->_filesystem->changePermissions($htaccessPath, 0644);
        }
    }

    /**
     * Get backup-specific data from model for each row
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $row = parent::_generateRow($filename);
        foreach ($this->_backup->load($row['basename'], $this->_baseDir)
            ->getData() as $key => $value) {
            $row[$key] = $value;
        }
        $row['size'] = $this->_filesystem->getFileSize($filename);
        $row['id'] = $row['time'] . '_' . $row['type'];
        return $row;
    }
}
