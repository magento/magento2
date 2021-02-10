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
 * @api
 * @since 100.3.0
 */
interface KeywordInterface extends ExtensibleDataInterface
{
    /**
     * Get ID
     *
     * @return int|null
     * @since 100.3.0
     */
    public function getId(): ?int;

    /**
     * Get the keyword
     *
     * @return string
     * @since 100.3.0
     */
    public function getKeyword(): string;

    /**
     * Get extension attributes
     *
     * @return \Magento\MediaGalleryApi\Api\Data\KeywordExtensionInterface|null
     * @since 100.3.0
     */
    public function getExtensionAttributes(): ?KeywordExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryApi\Api\Data\KeywordExtensionInterface|null $extensionAttributes
     * @return void
     * @since 100.3.0
     */
    public function setExtensionAttributes(?KeywordExtensionInterface $extensionAttributes): void;
}
