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
 * @since 100.4.0
 */
interface ContentAssetLinkInterface extends ExtensibleDataInterface
{
    /**
     * Return the object that represent content identity
     *
     * @return ContentIdentityInterface
     * @since 100.4.0
     */
    public function getContentId(): ContentIdentityInterface;

    /**
     * Array of assets related to the content entity
     *
     * @return int
     * @since 100.4.0
     */
    public function getAssetId(): int;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\MediaContentApi\Api\Data\ContentAssetLinkExtensionInterface|null
     * @since 100.4.0
     */
    public function getExtensionAttributes(): ?ContentAssetLinkExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaContentApi\Api\Data\ContentAssetLinkExtensionInterface|null $extensionAttributes
     * @return void
     * @since 100.4.0
     */
    public function setExtensionAttributes(?ContentAssetLinkExtensionInterface $extensionAttributes): void;
}
