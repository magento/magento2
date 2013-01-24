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
 * Theme files model class
 */
class Mage_Core_Model_Theme_Files extends Mage_Core_Model_Abstract
{
    /**
     * css file type
     */
    const TYPE_CSS = 'css';

    /**
     * @var Varien_Io_File
     */
    protected $_ioFile;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_design;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Mage_Core_Model_Event_Manager $eventDispatcher
     * @param Mage_Core_Model_Cache $cacheManager
     * @param Mage_Core_Model_Resource_Abstract $resource
     * @param Varien_Data_Collection_Db $resourceCollection
     * @param Varien_Io_File $ioFile
     * @param Magento_ObjectManager $objectManager
     * @param array $data
     */
    public function __construct(
        Mage_Core_Model_Event_Manager $eventDispatcher,
        Mage_Core_Model_Cache $cacheManager,
        Varien_Io_File $ioFile,
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Varien_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($eventDispatcher, $cacheManager, $resource, $resourceCollection, $data);

        $this->_ioFile = $ioFile;
        $this->_objectManager = $objectManager;
        $this->_design = $this->_objectManager->get('Mage_Core_Model_Design_Package');
    }

    /**
     * Theme files model initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_Core_Model_Resource_Theme_Files');
    }

    /**
     * Create/update/delete file after save
     * Delete file if only file is empty
     *
     * @return Mage_Core_Model_Theme_Files
     */
    protected function _afterSave()
    {
        if ($this->getContent()) {
            $this->_saveFile();
        } else {
            $this->_deleteFile();
        }
        return parent::_afterSave();
    }

    /**
     * Delete file form file system after delete form db
     *
     * @return Mage_Core_Model_Theme_Files
     */
    protected function _afterDelete()
    {
        $this->_deleteFile();

        return parent::_afterDelete();
    }

    /**
     * Create/update file in file system
     *
     * @return bool|int
     */
    protected function _saveFile()
    {
        $filePath = $this->getFilePath(true);
        $this->_ioFile->checkAndCreateFolder(dirname($filePath));
        $result = $this->_ioFile->write($filePath, $this->getContent());
        $this->_design->cleanMergedJsCss();
        return $result;
    }

    /**
     * Delete file form file system
     *
     * @return bool
     */
    protected function _deleteFile()
    {
        $result = $this->_ioFile->rm($this->getFilePath(true));
        $this->_design->cleanMergedJsCss();
        return $result;
    }

    /**
     * Return file path in file system
     *
     * @param bool $fullPath
     * @return string|bool
     */
    public function getFilePath($fullPath = false)
    {
        if (!$this->getId()) {
            return false;
        }
        $filePath = $this->getThemeId() . DIRECTORY_SEPARATOR . $this->getFileName();
        if ($fullPath) {
            $filePath = $this->_design->getCustomizationDir() . DIRECTORY_SEPARATOR . $filePath;
        }
        return $filePath;
    }
}
