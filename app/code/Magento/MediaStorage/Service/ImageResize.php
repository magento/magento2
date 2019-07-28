<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Service;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssertImageFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Framework\App\State;
use Magento\Framework\View\ConfigInterface as ViewConfig;
use \Magento\Catalog\Model\ResourceModel\Product\Image as ProductImage;
use Magento\Theme\Model\Config\Customization as ThemeCustomizationConfig;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Helper\File\Storage\Database;

/**
 * Image resize service.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImageResize
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var MediaConfig
     */
    private $imageConfig;

    /**
     * @var ProductImage
     */
    private $productImage;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var ParamsBuilder
     */
    private $paramsBuilder;

    /**
     * @var ViewConfig
     */
    private $viewConfig;

    /**
     * @var AssertImageFactory
     */
    private $assertImageFactory;

    /**
     * @var ThemeCustomizationConfig
     */
    private $themeCustomizationConfig;

    /**
     * @var Collection
     */
    private $themeCollection;

    /**
     * @var Filesystem
     */
    private $mediaDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Database
     */
    private $fileStorageDatabase;

    /**
     * @param State $appState
     * @param MediaConfig $imageConfig
     * @param ProductImage $productImage
     * @param ImageFactory $imageFactory
     * @param ParamsBuilder $paramsBuilder
     * @param ViewConfig $viewConfig
     * @param AssertImageFactory $assertImageFactory
     * @param ThemeCustomizationConfig $themeCustomizationConfig
     * @param Collection $themeCollection
     * @param Filesystem $filesystem
     * @param Database $fileStorageDatabase
     * @internal param ProductImage $gallery
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        State $appState,
        MediaConfig $imageConfig,
        ProductImage $productImage,
        ImageFactory $imageFactory,
        ParamsBuilder $paramsBuilder,
        ViewConfig $viewConfig,
        AssertImageFactory $assertImageFactory,
        ThemeCustomizationConfig $themeCustomizationConfig,
        Collection $themeCollection,
        Filesystem $filesystem,
        Database $fileStorageDatabase = null
    ) {
        $this->appState = $appState;
        $this->imageConfig = $imageConfig;
        $this->productImage = $productImage;
        $this->imageFactory = $imageFactory;
        $this->paramsBuilder = $paramsBuilder;
        $this->viewConfig = $viewConfig;
        $this->assertImageFactory = $assertImageFactory;
        $this->themeCustomizationConfig = $themeCustomizationConfig;
        $this->themeCollection = $themeCollection;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->filesystem = $filesystem;
        $this->fileStorageDatabase = $fileStorageDatabase ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(Database::class);
    }

    /**
     * Create resized images of different sizes from an original image.
     *
     * @param string $originalImageName
     * @throws NotFoundException
     */
    public function resizeFromImageName(string $originalImageName)
    {
        $mediastoragefilename = $this->imageConfig->getMediaPath($originalImageName);
        $originalImagePath = $this->mediaDirectory->getAbsolutePath($mediastoragefilename);

        if ($this->fileStorageDatabase->checkDbUsage() &&
            !$this->mediaDirectory->isFile($mediastoragefilename)
        ) {
            $this->fileStorageDatabase->saveFileToFilesystem($mediastoragefilename);
        }

        if (!$this->mediaDirectory->isFile($originalImagePath)) {
            throw new NotFoundException(__('Cannot resize image "%1" - original image not found', $originalImagePath));
        }
        foreach ($this->getViewImages($this->getThemesInUse()) as $viewImage) {
            $this->resize($viewImage, $originalImagePath, $originalImageName);
        }
    }

    /**
     * Create resized images of different sizes from themes.
     *
     * @param array|null $themes
     * @return \Generator
     * @throws NotFoundException
     */
    public function resizeFromThemes(array $themes = null): \Generator
    {
        $count = $this->productImage->getCountUsedProductImages();
        if (!$count) {
            throw new NotFoundException(__('Cannot resize images - product images not found'));
        }

        $productImages = $this->productImage->getUsedProductImages();
        $viewImages = $this->getViewImages($themes ?? $this->getThemesInUse());

        foreach ($productImages as $image) {
            $error = '';
            $originalImageName = $image['filepath'];

            $mediastoragefilename = $this->imageConfig->getMediaPath($originalImageName);
            $originalImagePath = $this->mediaDirectory->getAbsolutePath($mediastoragefilename);

            if ($this->mediaDirectory->isFile($originalImagePath)) {
                foreach ($viewImages as $viewImage) {
                    if ($this->fileStorageDatabase->checkDbUsage()) {
                        $this->fileStorageDatabase->saveFileToFilesystem($mediastoragefilename);
                    }
                    $this->resize($viewImage, $originalImagePath, $originalImageName);
                }
            } else {
                $error = __('Cannot resize image "%1" - original image not found', $originalImagePath);
            }

            yield ['filename' => $originalImageName, 'error' => $error] => $count;
        }
    }

    /**
     * Search the current theme.
     *
     * @return array
     */
    private function getThemesInUse(): array
    {
        $themesInUse = [];
        $registeredThemes = $this->themeCollection->loadRegisteredThemes();
        $storesByThemes = $this->themeCustomizationConfig->getStoresByThemes();
        $keyType = is_integer(key($storesByThemes)) ? 'getId' : 'getCode';
        foreach ($registeredThemes as $registeredTheme) {
            if (array_key_exists($registeredTheme->$keyType(), $storesByThemes)) {
                $themesInUse[] = $registeredTheme;
            }
        }
        return $themesInUse;
    }

    /**
     * Get view images data from themes.
     *
     * @param array $themes
     * @return array
     */
    private function getViewImages(array $themes): array
    {
        $viewImages = [];
        /** @var \Magento\Theme\Model\Theme $theme */
        foreach ($themes as $theme) {
            $config = $this->viewConfig->getViewConfig(
                [
                    'area' => Area::AREA_FRONTEND,
                    'themeModel' => $theme,
                ]
            );
            $images = $config->getMediaEntities('Magento_Catalog', ImageHelper::MEDIA_TYPE_CONFIG_NODE);
            foreach ($images as $imageId => $imageData) {
                $uniqIndex = $this->getUniqueImageIndex($imageData);
                $imageData['id'] = $imageId;
                $viewImages[$uniqIndex] = $imageData;
            }
        }
        return $viewImages;
    }

    /**
     * Get unique image index.
     *
     * @param array $imageData
     * @return string
     */
    private function getUniqueImageIndex(array $imageData): string
    {
        ksort($imageData);
        unset($imageData['type']);
        // phpcs:disable Magento2.Security.InsecureFunction
        return md5(json_encode($imageData));
    }

    /**
     * Make image.
     *
     * @param string $originalImagePath
     * @param array $imageParams
     * @return Image
     */
    private function makeImage(string $originalImagePath, array $imageParams): Image
    {
        $image = $this->imageFactory->create($originalImagePath);
        $image->keepAspectRatio($imageParams['keep_aspect_ratio']);
        $image->keepFrame($imageParams['keep_frame']);
        $image->keepTransparency($imageParams['keep_transparency']);
        $image->constrainOnly($imageParams['constrain_only']);
        $image->backgroundColor($imageParams['background']);
        $image->quality($imageParams['quality']);
        return $image;
    }

    /**
     * Resize image.
     *
     * @param array $viewImage
     * @param string $originalImagePath
     * @param string $originalImageName
     */
    private function resize(array $viewImage, string $originalImagePath, string $originalImageName)
    {
        $imageParams = $this->paramsBuilder->build($viewImage);
        $image = $this->makeImage($originalImagePath, $imageParams);
        $imageAsset = $this->assertImageFactory->create(
            [
                'miscParams' => $imageParams,
                'filePath' => $originalImageName,
            ]
        );

        if (isset($imageParams['watermark_file'])) {
            if ($imageParams['watermark_height'] !== null) {
                $image->setWatermarkHeight($imageParams['watermark_height']);
            }

            if ($imageParams['watermark_width'] !== null) {
                $image->setWatermarkWidth($imageParams['watermark_width']);
            }

            if ($imageParams['watermark_position'] !== null) {
                $image->setWatermarkPosition($imageParams['watermark_position']);
            }

            if ($imageParams['watermark_image_opacity'] !== null) {
                $image->setWatermarkImageOpacity($imageParams['watermark_image_opacity']);
            }

            $image->watermark($this->getWatermarkFilePath($imageParams['watermark_file']));
        }

        if ($imageParams['image_width'] !== null && $imageParams['image_height'] !== null) {
            $image->resize($imageParams['image_width'], $imageParams['image_height']);
        }
        $image->save($imageAsset->getPath());

        if ($this->fileStorageDatabase->checkDbUsage()) {
            $mediastoragefilename = $this->mediaDirectory->getRelativePath($imageAsset->getPath());
            $this->fileStorageDatabase->saveFile($mediastoragefilename);
        }
    }

    /**
     * Returns watermark file absolute path
     *
     * @param string $file
     * @return string
     */
    private function getWatermarkFilePath($file)
    {
        $path = $this->imageConfig->getMediaPath('/watermark/' . $file);
        return $this->mediaDirectory->getAbsolutePath($path);
    }
}
