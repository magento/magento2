<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\MediaGalleryMetadataApi\Api\Data\MetadataExtensionInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;

/**
 * Media asset metadata data transfer object
 */
class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $keywords;

    /**
     * @var MetadataExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @param null|string $title
     * @param null|string $description
     * @param null|array $keywords
     * @param MetadataExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?array $keywords = null,
        ?MetadataExtensionInterface $extensionAttributes = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->keywords = $keywords;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getKeywords(): ?array
    {
        return $this->keywords;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?MetadataExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(?MetadataExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
