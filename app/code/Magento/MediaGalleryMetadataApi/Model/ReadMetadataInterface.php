<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;

/**
 * Metadata reader
 */
interface ReadMetadataInterface
{
    /**
     * Read metadata from the file
     *
     * @param FileInterface $file
     * @return MetadataInterface
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function execute(FileInterface $file): MetadataInterface;
}
