<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsExtensionInterface;

/**
 * Asset Id and Keywords combination data object for bulk operations with keyword services
 */
class AssetKeywords implements AssetKeywordsInterface
{
    /**
     * @var int
     */
    private $assetId;

    /**
     * @var array
     */
    private $keywords;

    /**
     * @var AssetKeywordsExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param int $assetId
     * @param array $keywords
     * @param AssetKeywordsExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        int $assetId,
        array $keywords,
        ?AssetKeywordsExtensionInterface $extensionAttributes = null
    ) {
        $this->assetId = $assetId;
        $this->keywords = $keywords;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getAssetId(): int
    {
        return $this->assetId;
    }

    /**
     * @inheritdoc
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?AssetKeywordsExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(?AssetKeywordsExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
