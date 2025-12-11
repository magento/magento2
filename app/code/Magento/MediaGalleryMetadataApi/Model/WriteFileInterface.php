<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;

/**
 * File writer
 * @api
 */
interface WriteFileInterface
{
    /**
     * Write file to filesystem
     *
     * @param FileInterface $file
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function execute(FileInterface $file): void;
}
