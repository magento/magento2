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
     * SourceSelectionResult constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param array $sourceItemSelections
     * @param bool $isShippable
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        array $sourceItemSelections,
        bool $isShippable,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

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
    public function getExtensionAttributes(): ?SourceSelectionResultExtensionInterface
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
    public function setExtensionAttributes(SourceSelectionResultExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
