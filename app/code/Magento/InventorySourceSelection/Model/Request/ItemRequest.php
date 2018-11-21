<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\Request;

use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestExtensionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * @inheritdoc
 */
class ItemRequest extends AbstractExtensibleModel implements ItemRequestInterface
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $qty;

    /**
     * ItemRequest constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param string|null $sku
     * @param float|null $qty
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
        string $sku = null,
        float $qty = null,
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

        $this->sku = $sku;
        $this->qty = $qty;
    }

    /**
     * @inheritdoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritdoc
     */
    public function getQty(): float
    {
        return $this->qty;
    }

    /**
     * @inheritdoc
     */
    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @inheritdoc
     */
    public function setQty(float $qty): void
    {
        $this->qty = $qty;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?ItemRequestExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(ItemRequestInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(ItemRequestExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
