<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Keyword\Command;

/**
 * A command represents functionality to get a media gallery asset keywords filtered by media gallery asset id.
 * @deprecated 101.0.0 use \Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface instead
 * @see \Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface
 */
interface GetAssetKeywordsInterface
{
    /**
     * Get asset related keywords.
     *
     * @param int $assetId
     * @return \Magento\MediaGalleryApi\Api\Data\KeywordInterface[]
     */
    public function execute(int $assetId): array;
}
