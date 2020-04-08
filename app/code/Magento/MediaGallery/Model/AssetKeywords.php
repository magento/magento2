<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsExtensionInterface;

/**
 * Asset's Keywords
 */
class AssetKeywords extends AbstractExtensibleModel implements AssetKeywordsInterface
{
    private const ASSET_ID = 'asset_id';
    private const KEYWORDS = 'keywords';

    /**
     * @inheritdoc
     */
    public function getAssetId(): int
    {
        return (int) $this->getData(self::ASSET_ID);
    }

    /**
     * @inheritdoc
     */
    public function getKeywords(): array
    {
        return $this->getData(self::KEYWORDS);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): AssetKeywordsExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(AssetKeywordsExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
