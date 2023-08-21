<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\MediaGalleryApi\Api\Data\AssetExtensionInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;

/**
 * Media Gallery Asset
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Asset implements AssetInterface
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $source;

    /**
     * @var string|null
     */
    private $hash;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string|null
     */
    private $createdAt;

    /**
     * @var string|null
     */
    private $updatedAt;

    /**
     * @var AssetExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param string $path
     * @param string $contentType
     * @param int $width
     * @param int $height
     * @param int $size
     * @param int|null $id
     * @param string|null $title
     * @param string|null $description
     * @param string|null $source
     * @param string|null $hash
     * @param string|null $createdAt
     * @param string|null $updatedAt
     * @param AssetExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $path,
        string $contentType,
        int $width,
        int $height,
        int $size,
        ?int $id = null,
        ?string $title = null,
        ?string $description = null,
        ?string $source = null,
        ?string $hash = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?AssetExtensionInterface $extensionAttributes = null
    ) {
        $this->path = $path;
        $this->contentType = $contentType;
        $this->width = $width;
        $this->height = $height;
        $this->size = $size;
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->source = $source;
        $this->hash = $hash;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        return $this->path;
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
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @inheritdoc
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @inheritdoc
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @inheritdoc
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?AssetExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(?AssetExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
