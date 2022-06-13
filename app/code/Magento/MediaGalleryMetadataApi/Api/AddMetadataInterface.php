<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;

/**
 * Add metadata to asset file
 * @api
 */
interface AddMetadataInterface
{
    /**
     * Add metadata to the asset file
     *
     * @param string $path
     * @param MetadataInterface $metadata
     * @throws LocalizedException
     */
    public function execute(string $path, MetadataInterface $metadata): void;
}
