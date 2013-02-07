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
 * Theme Image model class
 */
class Mage_Core_Model_Theme_Image extends Varien_Object
{
    /**
     * Preview image width
     */
    const PREVIEW_IMAGE_WIDTH = 200;

    /**
     * Preview image height
     */
    const PREVIEW_IMAGE_HEIGHT = 200;

    /**
     * Preview image directory
     */
    const IMAGE_DIR_PREVIEW = 'preview';

    /**
     * Origin image directory
     */
    const IMAGE_DIR_ORIGIN = 'origin';

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * Initialize dependencies
     *
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Helper_Data $helper
     * @param Magento_Filesystem $filesystem
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_Core_Helper_Data $helper,
        Magento_Filesystem $filesystem
    ) {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_filesystem = $filesystem;
    }

    /**
     * Setter for theme object
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme_Image
     */
    public function setTheme($theme)
    {
        $this->_theme = $theme;
        return $this;
    }

    /**
     * Getter for theme object
     *
     * @return Mage_Core_Model_Theme
     * @throws BadMethodCallException
     */
    public function getTheme()
    {
        if (null === $this->_theme) {
            throw new BadMethodCallException('Theme was not set');
        }
        return $this->_theme;
    }

    /**
     * Getter for theme preview image
     *
     * @return string
     */
    public function getPreviewImage()
    {
        return $this->getTheme()->getPreviewImage();
    }

    /**
     * Setter for theme preview image
     *
     * @param string $imageName
     * @return Mage_Core_Model_Theme_Image
     */
    public function setPreviewImage($imageName)
    {
        $this->getTheme()->setPreviewImage($imageName);
        return $this;
    }

    /**
     * Save preview image
     *
     * @return Mage_Core_Model_Theme_Image
     */
    public function savePreviewImage()
    {
        if (!$this->getPreviewImage() || !$this->getTheme()->getThemeDirectory()) {
            return $this;
        }
        $currentWorkingDir = getcwd();

        chdir($this->getTheme()->getThemeDirectory());

        $imagePath = realpath($this->getPreviewImage());

        if (0 === strpos($imagePath, $this->getTheme()->getThemeDirectory())) {
            $this->createPreviewImage($imagePath);
        }

        chdir($currentWorkingDir);

        return $this;
    }

    /**
     * Get directory path for origin image
     *
     * @return string
     */
    public function getImagePathOrigin()
    {
        return $this->_getPreviewImagePublishedRootDir() . DIRECTORY_SEPARATOR . self::IMAGE_DIR_ORIGIN;
    }

    /**
     * Get themes root directory absolute path
     *
     * @return string
     */
    protected function _getPreviewImagePublishedRootDir()
    {
        /** @var $design Mage_Core_Model_Design_Package */
        $design = $this->_objectManager->get('Mage_Core_Model_Design_Package');
        $dirPath = $design->getPublicDir();
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->ensureDirectoryExists($dirPath);
        $this->_filesystem->setWorkingDirectory($dirPath);
        return $dirPath;
    }

    /**
     * Get preview image directory url
     *
     * @return string
     */
    public function getPreviewImageDirectoryUrl()
    {
        return $this->_objectManager->get('Mage_Core_Model_App')->getStore()->getBaseUrl(
            Mage_Core_Model_Store::URL_TYPE_MEDIA
        ) . Mage_Core_Model_Dir::THEME . '/' . self::IMAGE_DIR_PREVIEW . '/';
    }

    /**
     * Upload and create preview image
     *
     * @param string $scope the request key for file
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function uploadPreviewImage($scope)
    {
        $adapter  = new Zend_File_Transfer_Adapter_Http();
        if (!$adapter->isUploaded($scope)) {
            return false;
        }
        if (!$adapter->isValid($scope)) {
            Mage::throwException($this->_helper->__('Uploaded image is not valid'));
        }
        $upload = new Varien_File_Uploader($scope);
        $upload->setAllowCreateFolders(true);
        $upload->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'xbm', 'wbmp'));
        $upload->setAllowRenameFiles(true);
        $upload->setFilesDispersion(false);

        if (!$upload->save($this->getImagePathOrigin())) {
            Mage::throwException($this->_helper->__('Image can not be saved.'));
        }

        $fileName = $this->getImagePathOrigin() . DS . $upload->getUploadedFileName();
        $this->removePreviewImage()->createPreviewImage($fileName);
        $this->_filesystem->delete($fileName);
        return true;
    }

    /**
     * Create preview image
     *
     * @param string $imagePath
     * @return string
     */
    public function createPreviewImage($imagePath)
    {
        $adapter = $this->_helper->getImageAdapterType();
        $image = new Varien_Image($imagePath, $adapter);
        $image->keepTransparency(true);
        $image->constrainOnly(true);
        $image->keepFrame(true);
        $image->keepAspectRatio(true);
        $image->backgroundColor(array(255, 255, 255));
        $image->resize(self::PREVIEW_IMAGE_WIDTH, self::PREVIEW_IMAGE_HEIGHT);

        $imageName = uniqid('preview_image_') . image_type_to_extension($image->getMimeType());
        $image->save($this->_getImagePathPreview(), $imageName);

        $this->setPreviewImage($imageName);

        return $imageName;
    }

    /**
     * Get directory path for preview image
     *
     * @return string
     */
    protected function _getImagePathPreview()
    {
        return $this->_getPreviewImagePublishedRootDir() . DIRECTORY_SEPARATOR . self::IMAGE_DIR_PREVIEW;
    }

    /**
     * Create preview image copy
     *
     * @return Mage_Core_Model_Theme_Image
     */
    public function createPreviewImageCopy()
    {
        $filePath = $this->_getImagePathPreview() . DIRECTORY_SEPARATOR . $this->getPreviewImage();
        $destinationFileName = Varien_File_Uploader::getNewFileName($filePath);
        $this->_filesystem->copy(
            $this->_getImagePathPreview() . DIRECTORY_SEPARATOR . $this->getPreviewImage(),
            $this->_getImagePathPreview() . DIRECTORY_SEPARATOR . $destinationFileName
        );
        $this->setPreviewImage($destinationFileName);
        return $this;
    }

    /**
     * Delete preview image
     *
     * @return Mage_Core_Model_Theme_Image
     */
    public function removePreviewImage()
    {
        $previewImage = $this->getPreviewImage();
        $this->setPreviewImage('');
        if ($previewImage) {
            $this->_filesystem->delete($this->_getImagePathPreview() . DIRECTORY_SEPARATOR . $previewImage);
        }
        return $this;
    }

    /**
     * Get url for themes preview image
     *
     * @return string
     */
    public function getPreviewImageUrl()
    {
        if (!$this->getPreviewImage()) {
            return $this->_getPreviewImageDefaultUrl();
        }
        return $this->getPreviewImageDirectoryUrl() . $this->getPreviewImage();
    }

    /**
     * Return default themes preview image url
     *
     * @return string
     */
    protected function _getPreviewImageDefaultUrl()
    {
        return $this->_objectManager->get('Mage_Core_Model_Design_Package')
            ->getViewFileUrl('Mage_Core::theme/default_preview.jpg');
    }
}
