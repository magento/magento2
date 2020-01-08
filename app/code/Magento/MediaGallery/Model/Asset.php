<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\MediaGalleryApi\Api\Data\AssetExtensionInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Media Gallery Asset
 */
class Asset extends AbstractExtensibleModel implements AssetInterface
{
    private const ID = 'id';
    private const PATH = 'path';
    private const TITLE = 'title';
    private const SOURCE = 'source';
    private const CONTENT_TYPE = 'content_type';
    private const WIDTH = 'width';
    private const HEIGHT = 'height';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        $id = $this->getData(self::ID);

        if (!$id) {
            return null;
        }

        return (int) $id;
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        return (string) $this->getData(self::PATH);
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function getSource(): ?string
    {
        return $this->getData(self::SOURCE);
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): string
    {
        return (string) $this->getData(self::CONTENT_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function getWidth(): int
    {
        return (int) $this->getData(self::WIDTH);
    }

    /**
     * @inheritdoc
     */
    public function getHeight(): int
    {
        return (int) $this->getData(self::HEIGHT);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): AssetExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(AssetExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
