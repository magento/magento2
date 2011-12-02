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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Directory contents block for Wysiwyg Images
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Content_Files extends Mage_Adminhtml_Block_Template
{
    /**
     * Files collection object
     *
     * @var Varien_Data_Collection_Filesystem
     */
    protected $_filesCollection;

    /**
     * Prepared Files collection for current directory
     *
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFiles()
    {
        if (! $this->_filesCollection) {
            $this->_filesCollection = Mage::getSingleton('Mage_Cms_Model_Wysiwyg_Images_Storage')
                ->getFilesCollection(
                    Mage::helper('Mage_Cms_Helper_Wysiwyg_Images')->getCurrentPath(), $this->_getMediaType()
                );
        }

        return $this->_filesCollection;
    }

    /**
     * Files collection count getter
     *
     * @return int
     */
    public function getFilesCount()
    {
        return $this->getFiles()->count();
    }

    /**
     * File idetifier getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileId(Varien_Object $file)
    {
        return $file->getId();
    }

    /**
     * File thumb URL getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileThumbUrl(Varien_Object $file)
    {
        return $file->getThumbUrl();
    }

    /**
     * File name URL getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileName(Varien_Object $file)
    {
        return $file->getName();
    }

    /**
     * Image file width getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileWidth(Varien_Object $file)
    {
        return $file->getWidth();
    }

    /**
     * Image file height getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileHeight(Varien_Object $file)
    {
        return $file->getHeight();
    }

    /**
     * File short name getter
     *
     * @param  Varien_Object $file
     * @return string
     */
    public function getFileShortName(Varien_Object $file)
    {
        return $file->getShortName();
    }

    public function getImagesWidth()
    {
        return Mage::getSingleton('Mage_Cms_Model_Wysiwyg_Images_Storage')->getConfigData('resize_width');
    }

    public function getImagesHeight()
    {
        return Mage::getSingleton('Mage_Cms_Model_Wysiwyg_Images_Storage')->getConfigData('resize_height');
    }

    /**
     * Return current media type based on request or data
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }
}
