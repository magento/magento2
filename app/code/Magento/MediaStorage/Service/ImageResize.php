<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Service;

use Generator;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssertImageFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Framework\App\State;
use Magento\Framework\View\ConfigInterface as ViewConfig;
use Magento\Catalog\Model\ResourceModel\Product\Image as ProductImage;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config\Customization as ThemeCustomizationConfig;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Helper\File\Storage\Database as FileStorageDatabase;
use Magento\Theme\Model\Theme;

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
     * @var ThemeCollection
     */
    private $themeCollection;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var FileStorageDatabase
     */
    private $fileStorageDatabase;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param State $appState
     * @param MediaConfig $imageConfig
     * @param ProductImage $productImage
     * @param ImageFactory $imageFactory
     * @param ParamsBuilder $paramsBuilder
     * @param ViewConfig $viewConfig
     * @param AssertImageFactory $assertImageFactory
     * @param ThemeCustomizationConfig $themeCustomizationConfig
     * @param ThemeCollection $themeCollection
     * @param Filesystem $filesystem
     * @param FileStorageDatabase $fileStorageDatabase
     * @param StoreManagerInterface $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
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
        ThemeCollection $themeCollection,
        Filesystem $filesystem,
        ?FileStorageDatabase $fileStorageDatabase = null,
        ?StoreManagerInterface $storeManager = null
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
        $this->fileStorageDatabase = $fileStorageDatabase ?:
            ObjectManager::getInstance()->get(FileStorageDatabase::class);
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
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
     * @param bool $skipHiddenImages
     * @return Generator
     * @throws NotFoundException
     */
    public function resizeFromThemes(?array $themes = null, bool $skipHiddenImages = false): Generator
    {
        $count = $this->getCountProductImages($skipHiddenImages);
        if (!$count) {
            throw new NotFoundException(__('Cannot resize images - product images not found'));
        }

        $productImages = $this->getProductImages($skipHiddenImages);
        $viewImages = $this->getViewImages($themes ?? $this->getThemesInUse());

        foreach ($productImages as $image) {
            $error = '';
            $originalImageName = $image['filepath'];

            $mediastoragefilename = $this->imageConfig->getMediaPath($originalImageName);
            $originalImagePath = $this->mediaDirectory->getAbsolutePath($mediastoragefilename);

            if ($this->fileStorageDatabase->checkDbUsage()) {
                $this->fileStorageDatabase->saveFileToFilesystem($mediastoragefilename);
            }
            if ($this->mediaDirectory->isFile($originalImagePath)) {
                try {
                    foreach ($viewImages as $viewImage) {
                        $this->resize($viewImage, $originalImagePath, $originalImageName);
                    }
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            } else {
                $error = __('Cannot resize image "%1" - original image not found', $originalImagePath);
            }

            yield ['filename' => $originalImageName, 'error' => (string) $error] => $count;
        }
    }

    /**
     * @param bool $skipHiddenImages
     * @return int
     */
    public function getCountProductImages(bool $skipHiddenImages = false): int
    {
        return $skipHiddenImages ?
            $this->productImage->getCountUsedProductImages() : $this->productImage->getCountAllProductImages();
    }

    /**
     * @param bool $skipHiddenImages
     * @return Generator
     */
    public function getProductImages(bool $skipHiddenImages = false): \Generator
    {
        return $skipHiddenImages ?
            $this->productImage->getUsedProductImages() : $this->productImage->getAllProductImages();
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
        $stores = $this->storeManager->getStores(true);
        /** @var Theme $theme */
        foreach ($themes as $theme) {
            $config = $this->viewConfig->getViewConfig(
                [
                    'area' => Area::AREA_FRONTEND,
                    'themeModel' => $theme,
                ]
            );
            $images = $config->getMediaEntities('Magento_Catalog', ImageHelper::MEDIA_TYPE_CONFIG_NODE);
            foreach ($images as $imageId => $imageData) {
                foreach ($stores as $store) {
                    $data = $this->paramsBuilder->build($imageData, (int) $store->getId());
                    $uniqIndex = $this->getUniqueImageIndex($data);
                    $data['id'] = $imageId;
                    $viewImages[$uniqIndex] = $data;
                }
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
     * @throws \InvalidArgumentException
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
     * Resize image if not already resized before
     *
     * @param array $imageParams
     * @param string $originalImagePath
     * @param string $originalImageName
     */
    private function resize(array $imageParams, string $originalImagePath, string $originalImageName)
    {
        unset($imageParams['id']);
        $imageAsset = $this->assertImageFactory->create(
            [
                'miscParams' => $imageParams,
                'filePath' => $originalImageName,
            ]
        );
        $imageAssetPath = $imageAsset->getPath();
        $usingDbAsStorage = $this->fileStorageDatabase->checkDbUsage();
        $mediaStorageFilename = $this->mediaDirectory->getRelativePath($imageAssetPath);

        $alreadyResized = $usingDbAsStorage ?
            $this->fileStorageDatabase->fileExists($mediaStorageFilename) :
            $this->mediaDirectory->isFile($imageAssetPath);

        if (!$alreadyResized) {
            $this->generateResizedImage(
                $imageParams,
                $originalImagePath,
                $imageAssetPath,
                $usingDbAsStorage,
                $mediaStorageFilename
            );
        }
    }

    /**
     * Generate resized image
     *
     * @param array $imageParams
     * @param string $originalImagePath
     * @param string $imageAssetPath
     * @param bool $usingDbAsStorage
     * @param string $mediaStorageFilename
     */
    private function generateResizedImage(
        array $imageParams,
        string $originalImagePath,
        string $imageAssetPath,
        bool $usingDbAsStorage,
        string $mediaStorageFilename
    ) {
        $image = $this->makeImage($originalImagePath, $imageParams);

        if ($imageParams['image_width'] !== null && $imageParams['image_height'] !== null) {
            $image->resize($imageParams['image_width'], $imageParams['image_height']);
        }

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

        $image->save($imageAssetPath);

        if ($usingDbAsStorage) {
            $this->fileStorageDatabase->saveFile($mediaStorageFilename);
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
