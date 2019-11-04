<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Model\Keyword\Command;

/**
 * Interface SaveAssetKeywordsInterface
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
