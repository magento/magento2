<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Get media gallery assets by paths in media storage
 * @api
 */
interface GetAssetsByPathsInterface
{
    /**
     * Get media asset list
     *
     * @param string[] $paths
     * @return \Magento\MediaGalleryApi\Api\Data\AssetInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $paths): array;
}
