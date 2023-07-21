<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Image;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\ConfigInterface;

/**
 * Delete image from cache
 */
class RemoveDeletedImagesFromCache
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $presentationConfig;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var Config
     */
    private Config $mediaConfig;

    /**
     * @var WriteInterface
     */
    private WriteInterface $mediaDirectory;

    /**
     * @var ParamsBuilder
     */
    private ParamsBuilder $imageParamsBuilder;

    /**
     * @var Image
     */
    private Image $imageHelper;

    /**
     * @var ConvertImageMiscParamsToReadableFormat
     */
    private ConvertImageMiscParamsToReadableFormat $convertImageMiscParamsToReadableFormat;

    /**
     * @param ConfigInterface $presentationConfig
     * @param EncryptorInterface $encryptor
     * @param Config $mediaConfig
     * @param Filesystem $filesystem
     * @param ParamsBuilder $imageParamsBuilder
     * @param Image $imageHelper
     * @param ConvertImageMiscParamsToReadableFormat $convertImageMiscParamsToReadableFormat
     * @throws FileSystemException
     */
    public function __construct(
        ConfigInterface $presentationConfig,
        EncryptorInterface $encryptor,
        Config $mediaConfig,
        Filesystem $filesystem,
        ParamsBuilder $imageParamsBuilder,
        Image $imageHelper,
        ConvertImageMiscParamsToReadableFormat $convertImageMiscParamsToReadableFormat
    ) {
        $this->presentationConfig = $presentationConfig;
        $this->encryptor = $encryptor;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->imageHelper = $imageHelper;
        $this->convertImageMiscParamsToReadableFormat = $convertImageMiscParamsToReadableFormat;
    }

    /**
     * Remove deleted images from cache.
     *
     * @param Product $product
     * @param array $files
     * @return null
     * @throws FileSystemException
     */
    public function removeDeletedImagesFromCache(Product $product, array $files)
    {
        $this->imageHelper->init($product, 'product_page_image_small');
        $imageArguments = $this->presentationConfig
            ->getViewConfig(['area' => \Magento\Framework\App\Area::AREA_FRONTEND])
            ->getMediaAttributes(
                'Magento_Catalog',
                Image::MEDIA_TYPE_CONFIG_NODE,
                'product_page_image_small'
            );

        $imageMiscParams = $this->imageParamsBuilder->build($imageArguments);

        if (isset($imageMiscParams['image_type'])) {
            unset($imageMiscParams['image_type']);
        }

        $cacheId = $this->encryptor->hash(
            implode('_', $this->convertImageMiscParamsToReadableFormat
                ->convertImageMiscParamsToReadableFormat($imageMiscParams)),
            Encryptor::HASH_VERSION_MD5
        );

        $catalogPath = $this->mediaConfig->getBaseMediaPath();

        foreach ($files as $filePath) {
            $this->mediaDirectory->delete(
                $catalogPath . '/cache/' . $cacheId . '/' . $filePath
            );
        }
        return null;
    }
}
