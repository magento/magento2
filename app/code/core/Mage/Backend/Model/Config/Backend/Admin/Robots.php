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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Config backend model for robots.txt
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Config_Backend_Admin_Robots extends Mage_Core_Model_Config_Data
{
    /**
     * Return content of default robot.txt
     *
     * @return bool|string
     */
    protected function _getDefaultValue()
    {
        $fileIo = $this->_getFileObject();
        $file = $this->_getRobotsTxtFilePath();
        if ($fileIo->fileExists($file)) {
            $fileIo->open(array('path' => $fileIo->getDestinationFolder($file)));
            return $fileIo->read($file);
        }
        return false;
    }

    /**
     * Load default content from robots.txt if customer does not define own
     *
     * @return Mage_Backend_Model_Config_Backend_Admin_Robots
     */
    protected function _afterLoad()
    {
        if (!(string) $this->getValue()) {
            $this->setValue($this->_getDefaultValue());
        }

        return parent::_afterLoad();
    }

    /**
     * Check and process robots file
     *
     * @return Mage_Backend_Model_Config_Backend_Admin_Robots
     */
    protected function _afterSave()
    {
        if ($this->getValue()) {
            $file = $this->_getRobotsTxtFilePath();
            $fileIo = $this->_getFileObject();
            $fileIo->open(array('path' => $fileIo->getDestinationFolder($file)));
            $fileIo->write($file, $this->getValue());
        }

        return parent::_afterSave();
    }

    /**
     * Get path to robots.txt
     *
     * @return string
     */
    protected function _getRobotsTxtFilePath()
    {
        return $this->_getFileObject()->getCleanPath(Mage::getBaseDir() . DS . 'robots.txt');
    }

    /**
     * Get file io
     *
     * @return Varien_Io_File
     */
    protected function _getFileObject()
    {
        return new Varien_Io_File();
    }
}
