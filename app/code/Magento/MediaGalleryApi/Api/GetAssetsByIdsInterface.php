<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Get media gallery assets by id attribute
 * @api
 */
interface GetAssetsByIdsInterface
{
    /**
     * Get media asset by id
     *
     * @param int[] $ids
     * @return \Magento\MediaGalleryApi\Api\Data\AssetInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $ids): array;
}
