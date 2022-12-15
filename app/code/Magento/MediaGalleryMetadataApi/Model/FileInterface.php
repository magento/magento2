<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Model;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\MediaGalleryMetadataApi\Model\FileExtensionInterface;

/**
 * File internal data transfer object
 * @api
 */
interface FileInterface extends ExtensibleDataInterface
{
    /**
     * Get file path
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Get metadata sections
     *
     * @return SegmentInterface[]
     */
    public function getSegments(): array;

    /**
     * Get extension attributes
     *
     * @return \Magento\MediaGalleryMetadataApi\Model\FileExtensionInterface|null
     */
    public function getExtensionAttributes(): ?FileExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryMetadataApi\Model\FileExtensionInterface|null $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(?FileExtensionInterface $extensionAttributes): void;
}
