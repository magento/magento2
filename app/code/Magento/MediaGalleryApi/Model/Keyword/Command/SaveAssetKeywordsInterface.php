<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Keyword\Command;

/**
 * A command represents the media gallery asset keywords save operation.
 * @deprecated 101.0.0 use \Magento\MediaGalleryApi\Api\SaveAssetsKeywordsInterface instead
 * @see \Magento\MediaGalleryApi\Api\SaveAssetsKeywordsInterface
 */
interface SaveAssetKeywordsInterface
{
    /**
     * Save asset keywords.
     *
     * @param \Magento\MediaGalleryApi\Api\Data\KeywordInterface[] $keywords
     * @param int $assetId
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(array $keywords, int $assetId): void;
}
