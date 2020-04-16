<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api\Data;

use Magento\MediaGalleryApi\Api\Data\AssetKeywordsExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for asset's keywords aggregation
 * @api
 */
interface AssetKeywordsInterface extends ExtensibleDataInterface
{
    /**
     * Get ID
     *
     * @return int
     */
    public function getAssetId(): int;

    /**
     * Get the keyword
     *
     * @return KeywordInterface[]
     */
    public function getKeywords(): array;

    /**
     * Get extension attributes
     *
     * @return \Magento\MediaGalleryApi\Api\Data\AssetKeywordsExtensionInterface|null
     */
    public function getExtensionAttributes(): ?AssetKeywordsExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryApi\Api\Data\AssetKeywordsExtensionInterface|null $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(?AssetKeywordsExtensionInterface $extensionAttributes): void;
}
