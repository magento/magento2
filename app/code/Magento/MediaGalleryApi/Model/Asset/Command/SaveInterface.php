<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Asset\Command;

use Magento\MediaGalleryApi\Api\Data\AssetInterface;

/**
 * A command which executes the media gallery asset save operation.
 */
interface SaveInterface
{
    /**
     * Save media asset
     *
     * @param \Magento\MediaGalleryApi\Api\Data\AssetInterface $mediaAsset
     *
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(AssetInterface $mediaAsset): int;
}
