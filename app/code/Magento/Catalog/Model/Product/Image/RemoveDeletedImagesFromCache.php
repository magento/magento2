<?php
/************************************************************************
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Image;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\ConfigInterface;

/**
 * Delete image from cache
 */
class RemoveDeletedImagesFromCache
{
    /**
     * Current hashing algorithm
     */
    private const HASH_ALGORITHM = 'md5';

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
     * @var ConvertImageMiscParamsToReadableFormat
     */
    private ConvertImageMiscParamsToReadableFormat $convertImageMiscParamsToReadableFormat;

    /**
     * @param ConfigInterface $presentationConfig
     * @param EncryptorInterface $encryptor
     * @param Config $mediaConfig
     * @param Filesystem $filesystem
     * @param ParamsBuilder $imageParamsBuilder
     * @param ConvertImageMiscParamsToReadableFormat $convertImageMiscParamsToReadableFormat
     */
    public function __construct(
        ConfigInterface $presentationConfig,
        EncryptorInterface $encryptor,
        Config $mediaConfig,
        Filesystem $filesystem,
        ParamsBuilder $imageParamsBuilder,
        ConvertImageMiscParamsToReadableFormat $convertImageMiscParamsToReadableFormat
    ) {
        $this->presentationConfig = $presentationConfig;
        $this->encryptor = $encryptor;
        $this->mediaConfig = $mediaConfig;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->convertImageMiscParamsToReadableFormat = $convertImageMiscParamsToReadableFormat;
    }

    /**
     * Remove deleted images from cache.
     *
     * @param array $files
     *
     * @return void
     */
    public function removeDeletedImagesFromCache(array $files): void
    {
        if (count($files) === 0) {
            return;
        }
        $images = $this->presentationConfig
            ->getViewConfig(['area' => \Magento\Framework\App\Area::AREA_FRONTEND])
            ->getMediaEntities(
                'Magento_Catalog',
                Image::MEDIA_TYPE_CONFIG_NODE
            );

        $catalogPath = $this->mediaConfig->getBaseMediaPath();

        foreach ($images as $imageData) {
            $imageMiscParams = $this->imageParamsBuilder->build($imageData);

            if (isset($imageMiscParams['image_type'])) {
                unset($imageMiscParams['image_type']);
            }

            $cacheId = hash(
                self::HASH_ALGORITHM,
                implode('_', $this->convertImageMiscParamsToReadableFormat
                ->convertImageMiscParamsToReadableFormat($imageMiscParams))
            );

            foreach ($files as $filePath) {
                $this->mediaDirectory->delete(
                    $catalogPath . '/cache/' . $cacheId . '/' . $filePath
                );
            }
        }
    }
}
