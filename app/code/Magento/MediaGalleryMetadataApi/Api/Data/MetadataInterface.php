<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataExtensionInterface;

/**
 * Media asset metadata data transfer object
 */
interface MetadataInterface extends ExtensibleDataInterface
{
    /**
     * Get asset title
     *
     * @return null|string
     */
    public function getTitle(): ?string;

    /**
     * Get asset description
     *
     * @return null|string
     */
    public function getDescription(): ?string;

    /**
     * Get asset keywords
     *
     * @return null|array
     */
    public function getKeywords(): ?array;

    /**
     * Get extension attributes
     *
     * @return \Magento\MediaGalleryMetadataApi\Api\Data\MetadataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?MetadataExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryMetadataApi\Api\Data\MetadataExtensionInterface|null $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(?MetadataExtensionInterface $extensionAttributes): void;
}
