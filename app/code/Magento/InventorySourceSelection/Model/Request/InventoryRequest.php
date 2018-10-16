<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelection\Model\Request;

use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * @inheritdoc
 */
class InventoryRequest extends AbstractExtensibleModel implements InventoryRequestInterface
{
    /**
     * @var int
     */
    private $stockId;

    /**
     * @var ItemRequestInterface[]
     */
    private $items;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param int $stockId
     * @param ItemRequestInterface[] $items
     */
    public function __construct(
        ItemRequestInterfaceFactory $itemRequestFactory,
        int $stockId,
        array $items
    ) {
        $this->stockId = $stockId;
        $this->itemRequestFactory = $itemRequestFactory;

        //TODO: Temporary fix for resolving issue with webApi (https://github.com/magento-engcom/msi/issues/1524)
        foreach ($items as $item) {
            if (false === $item instanceof ItemRequestInterface) {
                $this->items[] = $this->itemRequestFactory->create([
                    'sku' => $item['sku'],
                    'qty' => $item['qty']
                ]);
            } else {
                $this->items[] = $item;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getStockId(): int
    {
        return $this->stockId;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function setStockId(int $stockId): void
    {
        $this->stockId = $stockId;
    }

    /**
     * @inheritdoc
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?InventoryRequestExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(InventoryRequestInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(InventoryRequestExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
