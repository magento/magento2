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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme files abstract class
 */
abstract class Mage_Core_Model_Theme_Customization_Files_FilesAbstract extends Varien_Object
    implements Mage_Core_Model_Theme_Customization_CustomizationInterface
{
    /**
     * @var Mage_Core_Model_Theme_File
     */
    protected $_themeFiles;

    /**
     * Data for save
     *
     * @var mixed
     */
    protected $_dataForSave;

    /**
     * @param Mage_Core_Model_Theme_File $themeFiles
     */
    public function __construct(Mage_Core_Model_Theme_File $themeFiles)
    {
        $this->_themeFiles = $themeFiles;
    }

    /**
     * Setter for data for save
     *
     * @param mixed $data
     * @return Mage_Core_Model_Theme_Customization_Files_FilesAbstract
     */
    public function setDataForSave($data)
    {
        $this->_dataForSave = $data;
        return $this;
    }

    /**
     * Save data
     *
     * @param Mage_Core_Model_Theme_Customization_CustomizedInterface $theme
     * @return Mage_Core_Model_Theme_Customization_Files_FilesAbstract
     */
    public function saveData(Mage_Core_Model_Theme_Customization_CustomizedInterface $theme)
    {
        if (null !== $this->_dataForSave) {
            $this->_save($theme);
        }
        return $this;
    }

    /**
     * Save data
     *
     * @param Mage_Core_Model_Theme_Customization_CustomizedInterface $theme
     * @return Mage_Core_Model_Resource_Theme_File_Collection
     */
    public function getCollectionByTheme(Mage_Core_Model_Theme_Customization_CustomizedInterface $theme)
    {
        /** @var $filesCollection Mage_Core_Model_Resource_Theme_File_Collection */
        $filesCollection = $this->_themeFiles->getCollection()->addFilter('theme_id', $theme->getId())
            ->addFilter('file_type', $this->_getFileType());

        return $filesCollection;
    }

    /**
     * Return file type
     *
     * @return string
     */
    abstract protected function _getFileType();

    /**
     * Save data
     *
     * @param Mage_Core_Model_Theme $theme
     */
    abstract protected function _save($theme);
}
