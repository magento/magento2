<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\MediaGalleryMetadataApi\Model\SegmentExtensionInterface;
use Magento\MediaGalleryMetadataApi\Model\SegmentInterface;

/**
 * Segment internal data transfer object
 */
class Segment implements SegmentInterface
{
    /**
     * @var array
     */
    private $name;

    /**
     * @var string
     */
    private $data;

    /**
     * @var SegmentExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @param string $name
     * @param string $data
     * @param SegmentExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $name,
        string $data,
        ?SegmentExtensionInterface $extensionAttributes = null
    ) {
        $this->name = $name;
        $this->data = $data;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?SegmentExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(?SegmentExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
