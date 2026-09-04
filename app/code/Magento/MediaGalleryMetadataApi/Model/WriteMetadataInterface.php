<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Model;

use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;

/**
 * Metadata writer
 * @api
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
