<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api;

/**
 * Save keywords related to assets to the database
 * @api
 */
interface SaveAssetsKeywordsInterface
{
    /**
     * Save assets keywords
     *
     * @param \Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface[] $assetKeywords
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(array $assetKeywords): void;
}
