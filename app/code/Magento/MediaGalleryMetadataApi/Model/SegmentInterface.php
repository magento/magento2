<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadataApi\Model;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentExtensionInterface;

/**
 * Segment internal data transfer object
 */
interface SegmentInterface extends ExtensibleDataInterface
{
    /**
     * Get segment name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get segment data
     *
     * @return string
     */
    public function getData(): string;

    /**
     * Get extension attributes
     *
     * @return \Magento\MediaGalleryMetadataApi\Model\SegmentExtensionInterface|null
     */
    public function getExtensionAttributes(): ?SegmentExtensionInterface;

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryMetadataApi\Model\SegmentExtensionInterface|null $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(?SegmentExtensionInterface $extensionAttributes): void;
}
