<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Asset\Command;

/**
 * A command represents the get media gallery asset by using media gallery asset path as a filter parameter.
 */
interface GetByPathInterface
{
    /**
     * Get media asset list
     *
     * @param string $mediaFilePath
     *
     * @return \Magento\MediaGalleryApi\Api\Data\AssetInterface
     */
    public function execute(string $mediaFilePath): \Magento\MediaGalleryApi\Api\Data\AssetInterface;
}
