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
 * @package     Mage_Install
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Install data helper
 */
class Mage_Install_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * The list of var children directories that have to be cleaned before the install
     *
     * @var array
     */
    protected $_varSubFolders;

    /**
     * Delete all service folders from var directory
     */
    public function cleanVarFolder()
    {
        foreach ($this->getVarSubFolders() as $folder) {
            Varien_Io_File::rmdirRecursive($folder);
        }
    }

    /**
     * Retrieve the list of service directories located in var folder
     *
     * @return array
     */
    public function getVarSubFolders()
    {
        if ($this->_varSubFolders == null) {
            $this->_varSubFolders = array(
                Mage::getConfig()->getTempVarDir() . DS . 'session',
                Mage::getConfig()->getTempVarDir() . DS . 'cache',
                Mage::getConfig()->getTempVarDir() . DS . 'locks',
                Mage::getConfig()->getTempVarDir() . DS . 'log',
                Mage::getConfig()->getTempVarDir() . DS . 'report',
                Mage::getConfig()->getTempVarDir() . DS . 'maps'
            );
        }
        return $this->_varSubFolders;
    }

    /**
     * Set the list of service directories located in var folder
     *
     * @param array $varSubFolders
     * @return Mage_Install_Helper_Data
     */
    public function setVarSubFolders(array $varSubFolders)
    {
        $this->_varSubFolders = $varSubFolders;
        return $this;
    }
}
