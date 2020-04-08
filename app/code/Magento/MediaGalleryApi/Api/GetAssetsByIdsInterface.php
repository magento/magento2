<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * A command represents the get media gallery asset by using media gallery asset id as a filter parameter.
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
