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
use Psr\Log\LoggerInterface;

/**
 * Create folder by provided path
 */
class CreateByPath implements CreateDirectoriesByPathsInterface
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
     * @param LoggerInterface $logger
     * @param Storage $storage
     */
    public function __construct(
        LoggerInterface $logger,
        Storage $storage
    ) {
        $this->logger = $logger;
        $this->storage = $storage;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $failedPaths = [];
        foreach ($paths as $path) {
            try {
                $name = end(explode('/', $path));
                $this->storage->createDirectory(
                    $name,
                    $this->storage->getCmsWysiwygImages()->getStorageRoot() . $path
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
                    implode(' ,', $failedPaths)
                )
            );
        }
    }
}
