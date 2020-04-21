<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkExtensionInterface;

/**
 * Data interface representing the identificator of content. I.e. short description field of product entity with id 42
 * @api
 */
interface ContentAssetLinkInterface extends ExtensibleDataInterface
{
    /**
     * Return the object that represent content identity
     *
     * @return ContentIdentityInterface
     */
    public function getContentId(): ContentIdentityInterface;

    /**
     * Array of assets related to the content entity
     *
     * @return int
     */
    public function getAssetId(): int;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\MediaContentApi\Api\Data\ContentAssetLinkExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ContentAssetLinkExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaContentApi\Api\Data\ContentAssetLinkExtensionInterface|null $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(?ContentAssetLinkExtensionInterface $extensionAttributes): void;
}
