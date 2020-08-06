<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\MediaGalleryMetadataApi\Model\FileExtensionInterface;
use Magento\MediaGalleryMetadataApi\Model\FileInterface;

/**
 * File internal data transfer object
 */
class File implements FileInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $segments;

    /**
     * @var FileExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param string $path
     * @param array $segments
     * @param FileExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $path,
        array $segments,
        ?FileExtensionInterface $extensionAttributes = null
    ) {
        $this->path = $path;
        $this->segments = $segments;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getSegments(): array
    {
        return $this->segments;
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
    public function getExtensionAttributes(): ?FileExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(?FileExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
