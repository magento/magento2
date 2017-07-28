<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme Image model class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
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
     * @since 2.0.0
     */
    protected $mediaDirectory;

    /**
     * Root directory
     *
     * @var WriteInterface
     * @since 2.0.0
     */
    protected $rootDirectory;

    /**
     * Image factory
     *
     * @var \Magento\Framework\Image\Factory
     * @since 2.0.0
     */
    protected $imageFactory;

    /**
     * Image uploader
     *
     * @var Image\Uploader
     * @since 2.0.0
     */
    protected $uploader;

    /**
     * Theme image path
     *
     * @var Image\PathInterface
     * @since 2.0.0
     */
    protected $themeImagePath;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * Theme
     *
     * @var ThemeInterface
     * @since 2.0.0
     */
    protected $theme;

    /**
     * Width and height of preview image
     *
     * @var array
     * @since 2.0.0
     */
    protected $imageParams;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param Image\Uploader $uploader
     * @param Image\PathInterface $themeImagePath
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $imageParams
     * @param ThemeInterface $theme
     * @codingStandardsIgnoreStart
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\Factory $imageFactory,
        Image\Uploader $uploader,
        Image\PathInterface $themeImagePath,
        \Psr\Log\LoggerInterface $logger,
        array $imageParams = [self::PREVIEW_IMAGE_WIDTH, self::PREVIEW_IMAGE_HEIGHT],
        ThemeInterface $theme = null
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->rootDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->imageFactory = $imageFactory;
        $this->uploader = $uploader;
        $this->themeImagePath = $themeImagePath;
        $this->logger = $logger;
        $this->imageParams = $imageParams;
        $this->theme = $theme;
    }

    // @codingStandardsIgnoreEnd

    /**
     * Create preview image
     *
     * @param string $imagePath
     * @return $this
     * @since 2.0.0
     */
    public function createPreviewImage($imagePath)
    {
        list($imageWidth, $imageHeight) = $this->imageParams;
        $image = $this->imageFactory->create($imagePath);
        $image->keepTransparency(true);
        $image->constrainOnly(true);
        $image->keepFrame(true);
        $image->keepAspectRatio(true);
        $image->backgroundColor([255, 255, 255]);
        $image->resize($imageWidth, $imageHeight);

        $imageName = uniqid('preview_image_') . image_type_to_extension($image->getImageType());
        $image->save($this->themeImagePath->getImagePreviewDirectory(), $imageName);
        $this->theme->setPreviewImage($imageName);
        return $this;
    }

    /**
     * Create preview image duplicate
     *
     * @param ThemeInterface $theme
     * @return bool
     * @since 2.0.0
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
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $this->theme->setPreviewImage(null);
            $this->logger->critical($e);
        }
        return $isCopied;
    }

    /**
     * Delete preview image
     *
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getPreviewImagePath()
    {
        return $this->themeImagePath->getPreviewImagePath($this->theme);
    }

    /**
     * Get url of theme preview image
     *
     * @return string
     * @since 2.0.0
     */
    public function getPreviewImageUrl()
    {
        $previewImage = $this->theme->getPreviewImage();
        return empty($previewImage)
            ? $this->themeImagePath->getPreviewImageDefaultUrl()
            : $this->themeImagePath->getPreviewImageUrl($this->theme);
    }
}
