<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents a media gallery keyword. This object contains information about a media asset keyword entity.
 */
interface KeywordInterface extends ExtensibleDataInterface
{
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Get the keyword
     *
     * @return string
     */
    public function getKeyword(): string;

    /**
     * Get extension attributes
     *
     * @return \Magento\MediaGalleryApi\Api\Data\KeywordExtensionInterface|null
     */
    public function getExtensionAttributes(): KeywordExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryApi\Api\Data\KeywordExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(KeywordExtensionInterface $extensionAttributes): void;
}
