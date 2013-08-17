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
 * Theme Image Uploader
 */
class Mage_Core_Model_Theme_Image_Uploader
{
    /**
     * Allowed file extensions to upload
     *
     * @var array
     */
    protected  $_allowedExtensions = array('jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp');

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Zend_File_Transfer_Adapter_Http
     */
    protected $_transferAdapter;

    /**
     * @var Varien_File_UploaderFactory
     */
    protected $_uploaderFactory;


    /**
     * Initialize dependencies
     *
     * @param Mage_Core_Helper_Data $helper
     * @param Magento_Filesystem $filesystem
     * @param Zend_File_Transfer_Adapter_Http $transferAdapter
     * @param Varien_File_UploaderFactory $uploaderFactory
     */
    public function __construct(
        Mage_Core_Helper_Data $helper,
        Magento_Filesystem $filesystem,
        Zend_File_Transfer_Adapter_Http $transferAdapter,
        Varien_File_UploaderFactory $uploaderFactory
    ) {
        $this->_helper = $helper;
        $this->_filesystem = $filesystem;
        $this->_transferAdapter = $transferAdapter;
        $this->_uploaderFactory = $uploaderFactory;
    }

    /**
     * Upload preview image
     *
     * @param string $scope the request key for file
     * @param string $destinationPath path to upload directory
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function uploadPreviewImage($scope, $destinationPath)
    {
        if (!$this->_transferAdapter->isUploaded($scope)) {
            return false;
        }
        if (!$this->_transferAdapter->isValid($scope)) {
            throw new Mage_Core_Exception($this->_helper->__('Uploaded image is not valid'));
        }
        $upload = $this->_uploaderFactory->create(array('fileId' => $scope));
        $upload->setAllowCreateFolders(true);
        $upload->setAllowedExtensions($this->_allowedExtensions);
        $upload->setAllowRenameFiles(true);
        $upload->setFilesDispersion(false);

        if (!$upload->checkAllowedExtension($upload->getFileExtension())) {
            throw new Mage_Core_Exception($this->_helper->__('Invalid image file type.'));
        }
        if (!$upload->save($destinationPath)) {
            throw new Mage_Core_Exception($this->_helper->__('Image can not be saved.'));
        }
        return $destinationPath . DIRECTORY_SEPARATOR . $upload->getUploadedFileName();
    }
}
