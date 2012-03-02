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
 * Class to work media folder and database backups
 *
 * @category    Mage
 * @package     Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Media extends Mage_Backup_Snapshot
{
    /**
     * Implementation Rollback functionality for Snapshot
     *
     * @throws Mage_Exception
     * @return bool
     */
    public function rollback()
    {
        $this->_prepareIgnoreList();
        return parent::rollback();
    }

    /**
     * Implementation Create Backup functionality for Snapshot
     *
     * @throws Mage_Exception
     * @return bool
     */
    public function create()
    {
        $this->_prepareIgnoreList();
        return parent::create();
    }

    /**
     * Overlap getType
     *
     * @return string
     * @see Mage_Backup_Interface::getType()
     */
    public function getType()
    {
        return 'media';
    }

    /**
     * Add all folders and files except media and db backup to ignore list
     *
     * @return Mage_Backup_Media
     */
    protected function _prepareIgnoreList()
    {
        $iterator = new DirectoryIterator($this->getRootDir());

        foreach ($iterator as $item) {
            $filename = $item->getFilename();
            if (!in_array($filename, array('media', 'var'))) {
                $this->addIgnorePaths($item->getPathname());
            }
        }

        $iterator = new DirectoryIterator($this->getRootDir() . DS . 'var');
        $dbBackupFilename = $this->_getDbBackupManager()->getBackupFilename();

        foreach ($iterator as $item) {
            $filename = $item->getFilename();
            if ($filename != $dbBackupFilename) {
                $this->addIgnorePaths($item->getPathname());
            }
        }

        return $this;
    }
}
