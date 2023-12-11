<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Command;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaGalleryApi\Api\CreateDirectoriesByPathsInterface;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Psr\Log\LoggerInterface;

/**
 * Create directories by provided paths in the media storage
 */
class CreateByPaths implements CreateDirectoriesByPathsInterface
{
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
     * @param LoggerInterface $logger
     * @param Storage $storage
     * @param IsPathExcludedInterface $isPathExcluded
     */
    public function __construct(
        LoggerInterface $logger,
        Storage $storage,
        IsPathExcludedInterface $isPathExcluded
    ) {
        $this->logger = $logger;
        $this->storage = $storage;
        $this->isPathExcluded = $isPathExcluded;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $failedPaths = [];
        foreach ($paths as $path) {
            if ($this->isPathExcluded->execute($path)) {
                $failedPaths[] = $path;
                continue;
            }
            try {
                $path = $path !== null ? $path : '';
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
                    'Could not create directories: %paths',
                    [
                        'paths' => implode(' ,', $failedPaths)
                    ]
                )
            );
        }
    }
}
