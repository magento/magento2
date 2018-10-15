<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\Result;

use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultExtensionInterface;

/**
 * @inheritdoc
 */
class SourceSelectionResult extends AbstractExtensibleModel implements SourceSelectionResultInterface
{
    /**
     * @var SourceSelectionItemInterface[]
     */
    private $sourceItemSelections;

    /**
     * @var bool
     */
    private $isShippable;

    /**
     * @param SourceSelectionItemInterface[] $sourceItemSelections
     * @param bool $isShippable
     */
    public function __construct(array $sourceItemSelections, bool $isShippable)
    {
        $this->sourceItemSelections = $sourceItemSelections;
        $this->isShippable = $isShippable;
    }

    /**
     * @inheritdoc
     */
    public function getSourceSelectionItems(): array
    {
        return $this->sourceItemSelections;
    }

    /**
     * @inheritdoc
     */
    public function isShippable(): bool
    {
        return $this->isShippable;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(
                SourceSelectionResultInterface::class
            );
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceSelectionResultExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
