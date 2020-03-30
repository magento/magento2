<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Command;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaGalleryApi\Model\Directory\Command\CreateByPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Create folder by provided path
 */
class CreateByPath implements CreateByPathInterface
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
     * Create new directory by provided path
     *
     * @param string $path
     * @param string $name
     * @throws CouldNotSaveException
     */
    public function execute(string $path, string $name): void
    {
        try {
            $this->storage->createDirectory($name, $this->storage->getCmsWysiwygImages()->getStorageRoot() . $path);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __('Failed to create the folder: %error', ['error' => $exception->getMessage()]);
            throw new CouldNotSaveException($message, $exception);
        }
    }
}
