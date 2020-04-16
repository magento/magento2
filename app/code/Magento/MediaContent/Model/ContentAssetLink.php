<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterface;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkExtensionInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * Relation of the media asset to the media content
 */
class ContentAssetLink implements ContentAssetLinkInterface
{
    /**
     * @var ContentAssetLinkExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @var ContentIdentityInterface
     */
    private $contentIdentity;

    /**
     * @var int
     */
    private $assetId;

    /**
     * ContentAssetLink constructor.
     * @param int $assetId
     * @param ContentIdentityInterface $contentIdentity
     * @param ContentAssetLinkExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        int $assetId,
        ContentIdentityInterface $contentIdentity,
        ?ContentAssetLinkExtensionInterface $extensionAttributes = null
    ) {
        $this->assetId = $assetId;
        $this->contentIdentity = $contentIdentity;
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
    public function getContentId(): ContentIdentityInterface
    {
        return $this->contentIdentity;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?ContentAssetLinkExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(?ContentAssetLinkExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
