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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Rollback worker for rolling back via local filesystem
 *
 * @category    Mage
 * @package     Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Filesystem_Rollback_Fs extends Mage_Backup_Filesystem_Rollback_Abstract
{
    /**
     * Files rollback implementation via local filesystem
     *
     * @see Mage_Backup_Filesystem_Rollback_Abstract::run()
     * @throws Mage_Exception
     */
    public function run()
    {
        $snapshotPath = $this->_snapshot->getBackupPath();

        if (!is_file($snapshotPath) || !is_readable($snapshotPath)) {
            throw new Mage_Backup_Exception_CantLoadSnapshot('Cant load snapshot archive');
        }

        $fsHelper = new Mage_Backup_Filesystem_Helper();

        $filesInfo = $fsHelper->getInfo(
            $this->_snapshot->getRootDir(),
            Mage_Backup_Filesystem_Helper::INFO_WRITABLE,
            $this->_snapshot->getIgnorePaths()
        );

        if (!$filesInfo['writable']) {
            throw new Mage_Backup_Exception_NotEnoughPermissions(
                'Unable to make rollback because not all files are writable'
            );
        }

        $archiver = new Mage_Archive();

        /**
         * we need these fake initializations because all magento's files in filesystem will be deleted and autoloader
         * wont be able to load classes that we need for unpacking
         */
        new Mage_Archive_Tar();
        new Mage_Archive_Gz();
        new Mage_Archive_Helper_File('');
        new Mage_Archive_Helper_File_Gz('');

        $fsHelper->rm($this->_snapshot->getRootDir(), $this->_snapshot->getIgnorePaths());
        $archiver->unpack($snapshotPath, $this->_snapshot->getRootDir());
    }
}
