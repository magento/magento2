<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

use Magento\MediaGalleryUi\Model\InsertImageDataExtensionInterface;

/**
 * Class responsible to provide insert image details
 */
class InsertImageData implements InsertImageDataInterface
{
    /**
     * @var InsertImageDataExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $type;

    /**
     * InsertImageData constructor.
     *
     * @param string $content
     * @param int $size
     * @param string $type
     */
    public function __construct(string $content, int $size, string $type)
    {
        $this->content = $content;
        $this->size = $size;
        $this->type = $type;
    }

    /**
     * Returns a content (just a link or an html block) for inserting image to the content
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns size of requested file
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Returns MIME type of requested file
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get extension attributes
     *
     * @return \Magento\MediaGalleryUi\Model\InsertImageDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?InsertImageDataExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * Set extension attributes
     *
     * @param \Magento\MediaGalleryUi\Model\InsertImageDataExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(InsertImageDataExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
