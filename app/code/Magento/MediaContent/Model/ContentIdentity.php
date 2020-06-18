<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityExtensionInterface;

/**
 * @inheritdoc
 */
class ContentIdentity implements ContentIdentityInterface
{
    private $entityType;
    private $entityId;
    private $field;
    private $extensionAttributes;

    /**
     * ContentIdentity constructor.
     * @param string $entityType
     * @param string $entityId
     * @param string $field
     * @param ContentIdentityExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $entityType,
        string $entityId,
        string $field,
        ?ContentIdentityExtensionInterface $extensionAttributes = null
    ) {
        $this->entityType = $entityType;
        $this->entityId= $entityId;
        $this->field = $field;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @inheritdoc
     */
    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * @inheritdoc
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?ContentIdentityExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(?ContentIdentityExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }
}
