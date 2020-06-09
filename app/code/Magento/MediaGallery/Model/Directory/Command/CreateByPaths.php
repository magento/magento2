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
use Magento\MediaGalleryApi\Api\IsPathBlacklistedInterface;
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
     * @var IsPathBlacklistedInterface
     */
    private $isPathBlacklisted;

    /**
     * @param LoggerInterface $logger
     * @param Storage $storage
     * @param IsPathBlacklistedInterface $isPathBlacklisted
     */
    public function __construct(
        LoggerInterface $logger,
        Storage $storage,
        IsPathBlacklistedInterface $isPathBlacklisted
    ) {
        $this->logger = $logger;
        $this->storage = $storage;
        $this->isPathBlacklisted = $isPathBlacklisted;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $failedPaths = [];
        foreach ($paths as $path) {
            if ($this->isPathBlacklisted->execute($path)) {
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
                    'Could not save directories: %paths',
                    [
                        'paths' => implode(' ,', $failedPaths)
                    ]
                )
            );
        }
    }
}
