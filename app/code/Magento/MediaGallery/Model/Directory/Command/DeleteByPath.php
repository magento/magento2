<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Command;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaGalleryApi\Model\Directory\Command\DeleteByPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete directory from media storage by path
 */
class DeleteByPath implements DeleteByPathInterface
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
     * Removes directory and corresponding thumbnails directory from media storage if not in blacklist
     *
     * @param string $path
     * @throws CouldNotDeleteException
     */
    public function execute(string $path): void
    {
        try {
            $this->storage->deleteDirectory($this->storage->getCmsWysiwygImages()->getStorageRoot() . $path);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotDeleteException(__('Failed to delete the folder'), $exception);
        }
    }
}
