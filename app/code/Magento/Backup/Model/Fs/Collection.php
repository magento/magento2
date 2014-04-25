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
namespace Magento\Backup\Model\Fs;

/**
 * Backup data collection
 */
class Collection extends \Magento\Framework\Data\Collection\Filesystem
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_varDirectory;

    /**
     * Folder, where all backups are stored
     *
     * @var string
     */
    protected $_path = 'backups';

    /**
     * Backup data
     *
     * @var \Magento\Backup\Helper\Data
     */
    protected $_backupData = null;

    /**
     * Backup model
     *
     * @var \Magento\Backup\Model\Backup
     */
    protected $_backup = null;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Backup\Helper\Data $backupData
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Backup\Model\Backup $backup
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Backup\Helper\Data $backupData,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Backup\Model\Backup $backup
    ) {
        $this->_backupData = $backupData;
        parent::__construct($entityFactory);

        $this->_filesystem = $filesystem;
        $this->_backup = $backup;
        $this->_varDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::VAR_DIR);

        $this->_hideBackupsForApache();

        // set collection specific params
        $extensions = $this->_backupData->getExtensions();

        foreach ($extensions as $value) {
            $extensions[] = '(' . preg_quote($value, '/') . ')';
        }
        $extensions = implode('|', $extensions);

        $this->_varDirectory->create($this->_path);
        $path = rtrim($this->_varDirectory->getAbsolutePath($this->_path), '/') . '/';
        $this->setOrder(
            'time',
            self::SORT_ORDER_DESC
        )->addTargetDir(
            $path
        )->setFilesFilter(
            '/^[a-z0-9\-\_]+\.' . $extensions . '$/'
        )->setCollectRecursively(
            false
        );
    }

    /**
     * Create .htaccess file and deny backups directory access from web
     *
     * @return void
     */
    protected function _hideBackupsForApache()
    {
        $filename = '.htaccess';
        if (!$this->_varDirectory->isFile($filename)) {
            $this->_varDirectory->writeFile($filename, 'deny from all');
            $this->_varDirectory->changePermissions($filename, 0644);
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
        foreach ($this->_backup->load(
            $row['basename'],
            $this->_varDirectory->getAbsolutePath($this->_path)
        )->getData() as $key => $value) {
            $row[$key] = $value;
        }
        $row['size'] = $this->_varDirectory->stat($this->_varDirectory->getRelativePath($filename))['size'];
        $row['id'] = $row['time'] . '_' . $row['type'];
        return $row;
    }
}
