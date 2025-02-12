<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Model\ResourceModel\Sales\Order;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Tax;
use Magento\Sales\Model\Order\Tax\Item;
use Magento\Sales\Model\ResourceModel\Order\Tax\Collection as TaxCollection;
use Magento\Sales\Model\ResourceModel\Order\Tax\CollectionFactory as TaxCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item\Collection as TaxItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item\CollectionFactory as TaxItemCollectionFactory;
use Magento\Tax\Api\Data\OrderTaxInterface;
use Magento\Tax\Api\Data\OrderTaxItemInterface;
use Magento\Tax\Model\Api\Data\Converter;

/**
 * Saves order taxes
 */
class Relation implements RelationInterface
{
    /**
     * @param TaxCollectionFactory $taxCollectionFactory
     * @param TaxItemCollectionFactory $taxItemCollectionFactory
     * @param ConvertQuoteTaxToOrderTax $convertQuoteTaxToOrderTax
     * @param Converter $dataConverter
     */
    public function __construct(
        private readonly TaxCollectionFactory $taxCollectionFactory,
        private readonly TaxItemCollectionFactory $taxItemCollectionFactory,
        private readonly ConvertQuoteTaxToOrderTax $convertQuoteTaxToOrderTax,
        private readonly Converter $dataConverter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function processRelation(AbstractModel $object)
    {
        if (!$object instanceof Order) {
            return;
        }

        if ($object->getExtensionAttributes()?->getConvertingFromQuote()) {
            $this->convertQuoteTaxToOrderTax->execute($object);
            return;
        }

        $taxCollection = $this->taxCollectionFactory->create();
        $taxCollection->addFieldToFilter(
            OrderTaxInterface::ORDER_ID,
            ['eq' => $object->getEntityId()]
        );

        $taxItemCollection = $this->taxItemCollectionFactory->create();
        $taxItemCollection->addFieldToFilter(
            OrderTaxItemInterface::TAX_ID,
            ['in' => array_map(fn (Tax $taxModel) => $taxModel->getId(), $taxCollection->getItems())]
        );

        $taxCollection->each('isDeleted', [true]);
        $taxItemCollection->each('isDeleted', [true]);

        $taxes = $this->getTaxes($object, $taxCollection);
        $itemizedTaxes = $this->getItemizedTaxes($object, $taxItemCollection, $taxCollection);
        $additionalItemizedTaxes = $this->getAdditionalItemizedTaxes($object, $taxItemCollection, $taxCollection);
        $itemizedTaxesByTaxCode = $this->groupItemizedTaxesByTaxCode(
            array_merge($itemizedTaxes, $additionalItemizedTaxes)
        );

        foreach ($taxes as $tax) {
            /** @var Tax $taxModel */
            $taxModel = $taxCollection->getItemById($tax->getTaxId());
            if ($taxModel) {
                $taxModel->isDeleted(false);
            } else {
                $taxModel = $this->dataConverter->createTaxModel();
            }
            $tax->setOrderId($object->getEntityId());
            $this->dataConverter->hydrateTaxModel($taxModel, $tax);

            $taxModel->save();
            $taxItems = [
                ...$itemizedTaxesByTaxCode[$tax->getId()] ?? [],
                ...$itemizedTaxesByTaxCode[$tax->getCode()] ?? []
            ];
            unset($itemizedTaxesByTaxCode[$tax->getId()], $itemizedTaxesByTaxCode[$tax->getCode()]);
            foreach ($taxItems as $taxItem) {
                /** @var Item $taxItemModel */
                $taxItemModel = $taxItemCollection->getItemById($taxItem->getTaxItemId());
                if ($taxItemModel) {
                    $taxItemModel->isDeleted(false);
                } else {
                    $taxItemModel = $this->dataConverter->createTaxItemModel();
                    $taxItemCollection->addItem($taxItemModel);
                }
                $taxItem->setTaxId($taxModel->getId());
                $this->dataConverter->hydrateTaxItemModel($taxItemModel, $taxItem);
            }
        }
        $taxCollection->each('save');
        $taxItemCollection->each('save');
    }

    /**
     * Get all taxes associated with order
     *
     * @param Order $order
     * @param TaxCollection $taxCollection
     * @return OrderTaxInterface[]
     */
    private function getTaxes(Order $order, TaxCollection $taxCollection): array
    {
        $taxes = $order->getExtensionAttributes()?->getTaxes();
        if ($taxes === null) {
            // if taxes extension attribute is not set, then restore taxes from DB
            $taxes = [];
            foreach ($taxCollection as $taxModel) {
                $tax = $this->dataConverter->createTaxDataModel();
                $this->dataConverter->hydrateTaxDataModel($tax, $taxModel);
                $taxes[] = $tax;
            }
        }
        return $taxes;
    }

    /**
     * Get itemized taxes associated with order items
     *
     * @param Order $order
     * @param TaxItemCollection $taxItemCollection
     * @param TaxCollection $taxCollection
     * @return OrderTaxItemInterface[]
     */
    private function getItemizedTaxes(
        Order $order,
        TaxItemCollection $taxItemCollection,
        TaxCollection $taxCollection
    ): array {
        $itemizedTaxes = [];
        $orderItemIdsWithItemizedTaxes = [];
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->getExtensionAttributes()?->getItemizedTaxes() !== null) {
                foreach ($orderItem->getExtensionAttributes()->getItemizedTaxes() as $itemizedTax) {
                    $isProductTax = $itemizedTax->getTaxableItemType() === 'product';
                    $itemizedTax->setItemId($isProductTax ? $orderItem->getItemId() : null);
                    $itemizedTax->setAssociatedItemId($isProductTax ? null : $orderItem->getItemId());
                    $itemizedTaxes[] = $itemizedTax;
                }
                $orderItemIdsWithItemizedTaxes[] = (int) $orderItem->getItemId();
            }
        }

        foreach ($taxItemCollection as $taxItemModel) {
            $itemId = $this->getAssociatedOrderItemId($taxItemModel);
            if ($itemId === null || in_array($itemId, $orderItemIdsWithItemizedTaxes, true)) {
                continue;
            }
            $taxItem = $this->dataConverter->createTaxItemDataModel();
            $this->dataConverter->hydrateTaxItemDataModel($taxItem, $taxItemModel);
            $taxItem->setTaxCode(
                $taxCollection->getItemById($taxItem->getTaxId())->getData(OrderTaxInterface::CODE)
            );
            $itemizedTaxes[] = $taxItem;
        }

        return $itemizedTaxes;
    }

    /**
     * Get additional itemized taxes
     *
     * @param Order $order
     * @param TaxItemCollection $taxItemCollection
     * @param TaxCollection $taxCollection
     * @return OrderTaxItemInterface[]
     */
    private function getAdditionalItemizedTaxes(
        Order $order,
        TaxItemCollection $taxItemCollection,
        TaxCollection $taxCollection
    ): array {
        $additionalItemizedTaxes = $order->getExtensionAttributes()?->getAdditionalItemizedTaxes();
        if ($additionalItemizedTaxes === null) {
            $additionalItemizedTaxes = [];
            foreach ($taxItemCollection as $taxItemModel) {
                if ($this->getAssociatedOrderItemId($taxItemModel) !== null) {
                    continue;
                }
                $taxItem = $this->dataConverter->createTaxItemDataModel();
                $this->dataConverter->hydrateTaxItemDataModel($taxItem, $taxItemModel);
                $taxItem->setTaxCode(
                    $taxCollection->getItemById($taxItem->getTaxId())->getData(OrderTaxInterface::CODE)
                );
                $additionalItemizedTaxes[] = $taxItem;
            }
        }
        return $additionalItemizedTaxes;
    }

    /**
     * Group itemized taxes by tax id or tax code
     *
     * @param OrderTaxItemInterface[] $itemizedTaxes
     * @return OrderTaxItemInterface[][]
     */
    private function groupItemizedTaxesByTaxCode(array $itemizedTaxes): array
    {
        $itemizedTaxesByTaxCode = [];
        foreach ($itemizedTaxes as $itemizedTax) {
            $itemizedTaxesByTaxCode[$itemizedTax->getTaxId() ?: $itemizedTax->getTaxCode()][] = $itemizedTax;
        }
        return $itemizedTaxesByTaxCode;
    }

    /**
     * Get associated order item id
     *
     * @param Item $taxItemModel
     * @return int|null
     */
    private function getAssociatedOrderItemId(Item $taxItemModel): ?int
    {
        $orderItemId = $taxItemModel->getData(OrderTaxItemInterface::ITEM_ID)
            ?: $taxItemModel->getData(OrderTaxItemInterface::ASSOCIATED_ITEM_ID);
        return $orderItemId ? (int) $orderItemId : null;
    }
}
