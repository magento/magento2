<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\MediaGalleryApi\Api\Data\KeywordExtensionInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;

/**
 * Asset's Keyword
 */
class Keyword implements KeywordInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $keyword;

    /**
     * @var KeywordExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param string $keyword
     * @param int|null $id
     * @param KeywordExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $keyword,
        ?int $id = null,
        ?KeywordExtensionInterface $extensionAttributes = null
    ) {
        $this->keyword = $keyword;
        $this->id = $id;
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
    public function getKeyword(): string
    {
        return $this->keyword;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?KeywordExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(?KeywordExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
