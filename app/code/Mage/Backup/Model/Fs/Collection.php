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
 * @category    Mage
 * @package     Mage_Backup
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup data collection
 *
 * @category   Mage
 * @package    Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Model_Fs_Collection extends Varien_Data_Collection_Filesystem
{
    /**
     * Folder, where all backups are stored
     *
     * @var string
     */
    protected $_baseDir;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * Set collection specific parameters and make sure backups folder will exist
     *
     * @param Magento_Filesystem $filesystem
     */
    public function __construct(Magento_Filesystem $filesystem)
    {
        parent::__construct();

        $this->_baseDir = Mage::getBaseDir('var') . DS . 'backups';
        $this->_filesystem = $filesystem;
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->ensureDirectoryExists($this->_baseDir);
        $this->_filesystem->setWorkingDirectory($this->_baseDir);

        $this->_hideBackupsForApache();

        // set collection specific params
        $extensions = Mage::helper('Mage_Backup_Helper_Data')->getExtensions();

        foreach ($extensions as $value) {
            $extensions[] = '(' . preg_quote($value, '/') . ')';
        }
        $extensions = implode('|', $extensions);

        $this
            ->setOrder('time', self::SORT_ORDER_DESC)
            ->addTargetDir($this->_baseDir)
            ->setFilesFilter('/^[a-z0-9\-\_]+\.' . $extensions . '$/')
            ->setCollectRecursively(false)
        ;
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
        foreach (Mage::getSingleton('Mage_Backup_Model_Backup')->load($row['basename'], $this->_baseDir)
            ->getData() as $key => $value) {
            $row[$key] = $value;
        }
        $row['size'] = $this->_filesystem->getFileSize($filename);
        $row['id'] = $row['time'] . '_' . $row['type'];
        return $row;
    }
}
