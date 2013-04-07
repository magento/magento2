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
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme file uploader service
 *
 * @method Mage_Theme_Model_Uploader_Service setUploadedJsFile(Mage_Core_Model_Theme_File $file)
 * @method Mage_Core_Model_Theme_File getUploadedJsFile()
 */
class Mage_Theme_Model_Uploader_Service extends Mage_Core_Model_Abstract
{
    /**
     * Css file upload limit
     */
    const XML_PATH_CSS_UPLOAD_LIMIT = 'global/theme/css/upload_limit';

    /**
     * Js file upload limit
     */
    const XML_PATH_JS_UPLOAD_LIMIT = 'global/theme/js/upload_limit';

    /**
     * Uploaded file path
     *
     * @var string|null
     */
    protected $_filePath;

    /**
     * File system helper
     *
     * @var Varien_Io_File
     */
    protected $_fileIo;

    /**
     * File size
     *
     * @var Magento_File_Size
     */
    protected $_fileSize;

    /**
     * Files js model
     *
     * @var Mage_Core_Model_Theme_Customization_Files_Js
     */
    protected $_filesJs;

    /**
     * File uploader
     *
     * @var Mage_Core_Model_File_Uploader
     */
    protected $_uploader;

    /**
     * Object manager
     *
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Mage_Core_Model_Context $context
     * @param Varien_Io_File $fileIo
     * @param Magento_File_Size $fileSize
     * @param Mage_Core_Model_Theme_Customization_Files_Js $filesJs
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Resource_Abstract $resource
     * @param Varien_Data_Collection_Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Mage_Core_Model_Context $context,
        Varien_Io_File $fileIo,
        Magento_File_Size $fileSize,
        Mage_Core_Model_Theme_Customization_Files_Js $filesJs,
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Varien_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_fileIo = $fileIo;
        $this->_fileSize = $fileSize;
        $this->_filesJs = $filesJs;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $resource, $resourceCollection, $data);
    }

    /**
     * Upload css file
     *
     * @param string $file - Key in the $_FILES array
     * @return Mage_Theme_Model_Uploader_Service
     * @throws Mage_Core_Exception
     */
    public function uploadCssFile($file)
    {
        /** @var $fileUploader Mage_Core_Model_File_Uploader */
        $fileUploader = $this->_objectManager->create('Mage_Core_Model_File_Uploader', array('fileId' => $file));
        $fileUploader->setAllowedExtensions(array('css'));
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowCreateFolders(true);

        $isValidFileSize = $this->_validateFileSize($fileUploader->getFileSize(), $this->getCssUploadMaxSize());
        if (!$isValidFileSize) {
            throw new Mage_Core_Exception($this->_objectManager->get('Mage_Core_Helper_Data')->__(
                'CSS file size should be less than %sM.', $this->getCssUploadMaxSizeInMb()
            ));
        }

        $file = $fileUploader->validateFile();
        $this->setFilePath($file['tmp_name']);
        return $this;
    }

    /**
     * Upload js file
     *
     * @param string $file - Key in the $_FILES array
     * @param Mage_Core_Model_Theme $theme
     * @param bool $saveAsTmp
     * @return Mage_Theme_Model_Uploader_Service
     * @throws Mage_Core_Exception
     */
    public function uploadJsFile($file, $theme, $saveAsTmp = true)
    {
        /** @var $fileUploader Mage_Core_Model_File_Uploader */
        $fileUploader = $this->_objectManager->create('Mage_Core_Model_File_Uploader', array('fileId' => $file));
        $fileUploader->setAllowedExtensions(array('js'));
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setAllowCreateFolders(true);

        $isValidFileSize = $this->_validateFileSize($fileUploader->getFileSize(), $this->getJsUploadMaxSize());
        if (!$isValidFileSize) {
            throw new Mage_Core_Exception($this->_objectManager->get('Mage_Core_Helper_Data')->__(
                'JS file size should be less than %sM.', $this->getJsUploadMaxSizeInMb()
            ));
        }

        $file = $fileUploader->validateFile();
        $this->setFilePath($file['tmp_name']);
        $file['content'] = $this->getFileContent();

        $themeFile = $this->_filesJs->saveJsFile($theme, $file, $saveAsTmp);
        $this->setUploadedJsFile($themeFile);
        return $this;
    }

    /**
     * Get js files object
     *
     * @return Mage_Core_Model_Theme_Customization_Files_Js
     */
    public function getJsFiles()
    {
        return $this->_filesJs;
    }

    /**
     * Get uploaded file content
     *
     * @return string
     */
    public function getFileContent()
    {
        return $this->_fileIo->read($this->getFilePath());
    }

    /**
     * Get css upload max size
     *
     * @return int
     */
    public function getCssUploadMaxSize()
    {
        return $this->_getMaxUploadSize(self::XML_PATH_CSS_UPLOAD_LIMIT);
    }

    /**
     * Get js upload max size
     *
     * @return int
     */
    public function getJsUploadMaxSize()
    {
        return $this->_getMaxUploadSize(self::XML_PATH_JS_UPLOAD_LIMIT);
    }

    /**
     * Get max upload size
     *
     * @param string $node
     * @return int
     */
    protected function _getMaxUploadSize($node)
    {
        $maxCssUploadSize = $this->_fileSize->convertSizeToInteger(
            (string)Mage::getConfig()->getNode($node)
        );
        $maxIniUploadSize = $this->_fileSize->getMaxFileSize();
        return min($maxCssUploadSize, $maxIniUploadSize);
    }

    /**
     * Get css upload max size in megabytes
     *
     * @return float
     */
    public function getCssUploadMaxSizeInMb()
    {
         return $this->_fileSize->getFileSizeInMb($this->getCssUploadMaxSize());
    }

    /**
     * Get js upload max size in megabytes
     *
     * @return float
     */
    public function getJsUploadMaxSizeInMb()
    {
        return $this->_fileSize->getFileSizeInMb($this->getJsUploadMaxSize());
    }

    /**
     * Validate max file size
     *
     * @param int $fileSize
     * @param int $maxFileSize
     * @return bool
     */
    protected function _validateFileSize($fileSize, $maxFileSize)
    {
        if ($fileSize > $maxFileSize) {
            return false;
        }
        return true;
    }
}
