<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Command;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaGalleryApi\Api\CreateDirectoriesByPathsInterface;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Psr\Log\LoggerInterface;

/**
 * Create directories by provided paths in the media storage
 */
class CreateByPaths implements CreateDirectoriesByPathsInterface
{
    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var ScopeConfigInterface
     */
    private $coreConfig;

    /**
     * @param LoggerInterface $logger
     * @param Storage $storage
     * @param IsPathExcludedInterface $isPathExcluded
     * @param ScopeConfigInterface $coreConfig
     */
    public function __construct(
        LoggerInterface $logger,
        Storage $storage,
        IsPathExcludedInterface $isPathExcluded,
        ScopeConfigInterface $coreConfig = null
    ) {
        $this->logger = $logger;
        $this->storage = $storage;
        $this->isPathExcluded = $isPathExcluded;
        $this->coreConfig = $coreConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $failedPaths = [];
        $mediaGalleryImageFolders = $this->coreConfig->getValue(
            self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
            'default'
        );
        foreach ($paths as $path) {
            if ($this->isPathExcluded->execute($path)) {
                $failedPaths[] = $path;
                continue;
            }
            try {
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                $name = basename($path);
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                $folder = substr($path, 0, strrpos($path, $name));

                $this->storage->createDirectory(
                    $name,
                    $this->storage->getCmsWysiwygImages()->getStorageRoot() . $folder
                );
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                $failedPaths[] = $path;
            }
        }

        if (!empty($failedPaths)) {
            throw new CouldNotSaveException(
                __(
                    'Could not create directories: %paths,' .
                    ' You are allowed to create folders only in: %allowedPaths folders',
                    [
                        'paths' => implode(' ,', $failedPaths),
                        'allowedPaths' => implode(',', $mediaGalleryImageFolders)
                    ]
                )
            );
        }
    }
}
