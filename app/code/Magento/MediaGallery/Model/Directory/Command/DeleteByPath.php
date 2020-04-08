<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Command;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaGalleryApi\Api\DeleteDirectoriesByPathsInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete directory from media storage by path
 */
class DeleteByPath implements DeleteDirectoriesByPathsInterface
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
                $this->storage->deleteDirectory($this->storage->getCmsWysiwygImages()->getStorageRoot() . $path);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                $failedPaths[] = $path;
            }
        }

        if (!empty($failedPaths)) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete directories: %paths',
                    implode(' ,', $failedPaths)
                )
            );
        }
    }
}
