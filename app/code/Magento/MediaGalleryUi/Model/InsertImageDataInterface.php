<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

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
     * @return \Magento\MediaGalleryUi\Model\InsertImageDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?\Magento\MediaGalleryUi\Model\InsertImageDataExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryUi\Model\InsertImageDataExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\MediaGalleryUi\Model\InsertImageDataExtensionInterface $extensionAttributes
    ): void;
}
