<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * A command represents functionality to get a media gallery asset keywords filtered by media gallery asset id.
 * @api
 */
interface GetAssetsKeywordsInterface
{
    /**
     * Get assets related keywords.
     *
     * @param int[] $assetIds
     * @return \Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface[]
     */
    public function execute(array $assetIds): array;
}
