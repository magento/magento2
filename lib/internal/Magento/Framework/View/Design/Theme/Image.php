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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme Image model class
 */
class Image
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
     * Media directory
     *
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Root directory
     *
     * @var WriteInterface
     */
    protected $rootDirectory;

    /**
     * Image factory
     *
     * @var \Magento\Framework\Image\Factory
     */
    protected $imageFactory;

    /**
     * Image uploader
     *
     * @var Image\Uploader
     */
    protected $uploader;

    /**
     * Theme image path
     *
     * @var Image\PathInterface
     */
    protected $themeImagePath;

    /**
     * Logger
     *
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * Theme
     *
     * @var ThemeInterface
     */
    protected $theme;

    /**
     * Width and height of preview image
     *
     * @var array
     */
    protected $imageParams;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param Image\Uploader $uploader
     * @param Image\PathInterface $themeImagePath
     * @param \Magento\Framework\Logger $logger
     * @param array $imageParams
     * @param ThemeInterface $theme
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\Image\Factory $imageFactory,
        Image\Uploader $uploader,
        Image\PathInterface $themeImagePath,
        \Magento\Framework\Logger $logger,
        array $imageParams = array(self::PREVIEW_IMAGE_WIDTH, self::PREVIEW_IMAGE_HEIGHT),
        ThemeInterface $theme = null
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::MEDIA_DIR);
        $this->rootDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $this->imageFactory = $imageFactory;
        $this->uploader = $uploader;
        $this->themeImagePath = $themeImagePath;
        $this->logger = $logger;
        $this->imageParams = $imageParams;
        $this->theme = $theme;
    }

    /**
     * Create preview image
     *
     * @param string $imagePath
     * @return $this
     */
    public function createPreviewImage($imagePath)
    {
        list($imageWidth, $imageHeight) = $this->imageParams;
        $image = $this->imageFactory->create($imagePath);
        $image->keepTransparency(true);
        $image->constrainOnly(true);
        $image->keepFrame(true);
        $image->keepAspectRatio(true);
        $image->backgroundColor(array(255, 255, 255));
        $image->resize($imageWidth, $imageHeight);

        $imageName = uniqid('preview_image_') . image_type_to_extension($image->getMimeType());
        $image->save($this->themeImagePath->getImagePreviewDirectory(), $imageName);
        $this->theme->setPreviewImage($imageName);
        return $this;
    }

    /**
     * Create preview image duplicate
     *
     * @param ThemeInterface $theme
     * @return bool
     */
    public function createPreviewImageCopy(ThemeInterface $theme)
    {
        $previewDir = $this->themeImagePath->getImagePreviewDirectory();
        $sourcePath = $theme->getThemeImage()->getPreviewImagePath();
        $sourceRelativePath = $this->rootDirectory->getRelativePath($sourcePath);
        if (!$theme->getPreviewImage() && !$this->mediaDirectory->isExist($sourceRelativePath)) {
            return false;
        }
        $isCopied = false;
        try {
            $destinationFileName = \Magento\Framework\File\Uploader::getNewFileName($sourcePath);
            $targetRelativePath =  $this->mediaDirectory->getRelativePath($previewDir . '/' . $destinationFileName);
            $isCopied = $this->rootDirectory->copyFile($sourceRelativePath, $targetRelativePath, $this->mediaDirectory);
            $this->theme->setPreviewImage($destinationFileName);
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            $this->theme->setPreviewImage(null);
            $this->logger->logException($e);
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
        $previewImage = $this->theme->getPreviewImage();
        $this->theme->setPreviewImage(null);
        if ($previewImage) {
            $previewImagePath = $this->themeImagePath->getImagePreviewDirectory() . '/' . $previewImage;
            return $this->mediaDirectory->delete($this->mediaDirectory->getRelativePath($previewImagePath));
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
        $tmpDirPath = $this->themeImagePath->getTemporaryDirectory();
        $tmpFilePath = $this->uploader->uploadPreviewImage($scope, $tmpDirPath);
        if ($tmpFilePath) {
            if ($this->theme->getPreviewImage()) {
                $this->removePreviewImage();
            }
            $this->createPreviewImage($tmpFilePath);
            $this->mediaDirectory->delete($tmpFilePath);
        }
        return $this;
    }

    /**
     * Get path to preview image
     *
     * @return string
     */
    public function getPreviewImagePath()
    {
        return $this->themeImagePath->getPreviewImagePath($this->theme);
    }

    /**
     * Get url of theme preview image
     *
     * @return string
     */
    public function getPreviewImageUrl()
    {
        $previewImage = $this->theme->getPreviewImage();
        return empty($previewImage)
            ? $this->themeImagePath->getPreviewImageDefaultUrl()
            : $this->themeImagePath->getPreviewImageUrl($this->theme);
    }
}
