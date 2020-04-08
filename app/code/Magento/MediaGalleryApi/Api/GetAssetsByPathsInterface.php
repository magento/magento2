<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * A command represents the get media gallery asset by using media gallery asset path as a filter parameter.
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
