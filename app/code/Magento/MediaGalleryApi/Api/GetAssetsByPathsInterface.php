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
 * @since 101.0.0
 */
interface GetAssetsByPathsInterface
{
    /**
     * Get media asset list
     *
     * @param string[] $paths
     * @return \Magento\MediaGalleryApi\Api\Data\AssetInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 101.0.0
     */
    public function execute(array $paths): array;
}
