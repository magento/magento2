<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUiApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Class responsible to provide insert image details
 */
interface InsertImageDataInterface extends ExtensibleDataInterface
{
    /**
     * Returns a content (just a link or an html block) for inserting image to the content
     *
     * @return null|string
     */
    public function getContent(): ?string;

    /**
     * Returns size of requested file
     *
     * @return int
     */
    public function getSize(): int;

    /**
     * Returns MIME type of requested file
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get extension attributes
     *
     * @return \Magento\MediaGalleryUiApi\Api\InsertImageDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?\Magento\MediaGalleryUiApi\Api\InsertImageDataExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryUiApi\Api\InsertImageDataExtensionInterface|null $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        ?\Magento\MediaGalleryUiApi\Api\InsertImageDataExtensionInterface $extensionAttributes
    ): void;
}
