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
class Mage_Core_Model_Theme_Image
{
    /**
     * Preview image width
     */
    const PREVIEW_IMAGE_WIDTH = 800;

    /**
     * Preview image height
     */
    const PREVIEW_IMAGE_HEIGHT = 800;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Model_Image_Factory
     */
    protected $_imageFactory;

    /**
     * @var Mage_Core_Model_Theme_Image_UploaderProxy
     */
    protected $_uploader;

    /**
     * @var Mage_Core_Model_Theme_Image_Path
     */
    protected $_themeImagePath;

    /**
     * @var Mage_Core_Model_Logger
     */
    protected $_logger;

    /**
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * Initialize dependencies
     *
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Model_Image_Factory $imageFactory
     * @param Mage_Core_Model_Theme_Image_UploaderProxy $uploader
     * @param Mage_Core_Model_Theme_Image_Path $themeImagePath
     * @param Mage_Core_Model_Logger $logger
     * @param Mage_Core_Model_Theme $theme
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Core_Model_Image_Factory $imageFactory,
        Mage_Core_Model_Theme_Image_UploaderProxy $uploader,
        Mage_Core_Model_Theme_Image_Path $themeImagePath,
        Mage_Core_Model_Logger $logger,
        Mage_Core_Model_Theme $theme = null
    ) {
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->_uploader = $uploader;
        $this->_themeImagePath = $themeImagePath;
        $this->_logger = $logger;
        $this->_theme = $theme;
    }

    /**
     * Create preview image
     *
     * @param string $imagePath
     * @return $this
     */
    public function createPreviewImage($imagePath)
    {
        $image = $this->_imageFactory->create($imagePath);
        $image->keepTransparency(true);
        $image->constrainOnly(true);
        $image->keepFrame(true);
        $image->keepAspectRatio(true);
        $image->backgroundColor(array(255, 255, 255));
        $image->resize(self::PREVIEW_IMAGE_WIDTH, self::PREVIEW_IMAGE_HEIGHT);

        $imageName = uniqid('preview_image_') . image_type_to_extension($image->getMimeType());
        $image->save($this->_themeImagePath->getImagePreviewDirectory(), $imageName);
        $this->_theme->setPreviewImage($imageName);
        return $this;
    }

    /**
     * Create preview image duplicate
     *
     * @param string $previewImagePath
     * @return bool
     */
    public function createPreviewImageCopy($previewImagePath)
    {
        $previewDir = $this->_themeImagePath->getImagePreviewDirectory();
        $destinationFilePath = $previewDir . DIRECTORY_SEPARATOR . $previewImagePath;
        if (empty($previewImagePath) && !$this->_filesystem->has($destinationFilePath)) {
            return false;
        }

        $isCopied = false;
        try {
            $destinationFileName = Varien_File_Uploader::getNewFileName($destinationFilePath);
            $isCopied = $this->_filesystem->copy(
                $destinationFilePath,
                $previewDir . DIRECTORY_SEPARATOR . $destinationFileName
            );
            $this->_theme->setPreviewImage($destinationFileName);
        } catch (Exception $e) {
            $this->_logger->logException($e);
        }
        return $isCopied;
    }

    /**
     * Delete preview image
     *
     * @return bool
     */
    public function removePreviewImage()
    {
        $previewImage = $this->_theme->getPreviewImage();
        $this->_theme->setPreviewImage(null);
        if ($previewImage) {
            return $this->_filesystem->delete(
                $this->_themeImagePath->getImagePreviewDirectory() . DIRECTORY_SEPARATOR . $previewImage
            );
        }
        return false;
    }

    /**
     * Upload and create preview image
     *
     * @param string $scope the request key for file
     * @return $this
     */
    public function uploadPreviewImage($scope)
    {
        $tmpDirPath = $this->_themeImagePath->getTemporaryDirectory();
        $tmpFilePath = $this->_uploader->uploadPreviewImage($scope, $tmpDirPath);
        if ($tmpFilePath) {
            if ($this->_theme->getPreviewImage()) {
                $this->removePreviewImage();
            }
            $this->createPreviewImage($tmpFilePath);
            $this->_filesystem->delete($tmpFilePath);
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
        $previewImage = $this->_theme->getPreviewImage();
        if ($previewImage) {
            return $this->_themeImagePath->getPreviewImageDirectoryUrl() . $previewImage;
        }
        return $this->_themeImagePath->getPreviewImageDefaultUrl();
    }
}
