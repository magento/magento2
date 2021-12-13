<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Model;

use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;

/**
 * Metadata writer
 */
interface WriteMetadataInterface
{
    /**
     * Add metadata to the file
     *
     * @param FileInterface $file
     * @param MetadataInterface $data
     */
    public function execute(FileInterface $file, MetadataInterface $data): FileInterface;
}
