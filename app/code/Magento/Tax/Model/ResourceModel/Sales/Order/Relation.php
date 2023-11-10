<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Tax\Model\ResourceModel\Sales\Order;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Tax;
use Magento\Sales\Model\Order\Tax\Item;
use Magento\Sales\Model\Order\Tax\ItemFactory;
use Magento\Sales\Model\Order\TaxFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\CollectionFactory as TaxCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item\CollectionFactory as TaxItemCollectionFactory;
use Magento\Tax\Api\Data\OrderTaxInterface;
use Magento\Tax\Api\Data\OrderTaxItemInterface;

/**
 * Saves order taxes
 */
class Relation implements RelationInterface
{
    /**
     * @param TaxFactory $taxFactory
     * @param ItemFactory $taxItemFactory
     * @param TaxCollectionFactory $taxCollectionFactory
     * @param TaxItemCollectionFactory $taxItemCollectionFactory
     * @param ConvertQuoteTaxToOrderTax $convertQuoteTaxToOrderTax
     */
    public function __construct(
        private readonly TaxFactory $taxFactory,
        private readonly ItemFactory $taxItemFactory,
        private readonly TaxCollectionFactory $taxCollectionFactory,
        private readonly TaxItemCollectionFactory $taxItemCollectionFactory,
        private readonly ConvertQuoteTaxToOrderTax $convertQuoteTaxToOrderTax
    ) {
    }

    /**
     * Saves order taxes
     *
     * @param AbstractModel $object
     * @return void
     * @throws \Exception
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

        if ($object->getExtensionAttributes()?->getTaxes() === null) {
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

        foreach ($object->getExtensionAttributes()->getTaxes() as $tax) {
            /** @var Tax $taxModel */
            $taxModel = $taxCollection->getItemById($tax->getTaxId());
            if ($taxModel) {
                $taxModel->isDeleted(false);
            } else {
                $taxModel = $this->taxFactory->create();
            }

            $taxModel->addData([
                OrderTaxInterface::ORDER_ID => $object->getEntityId(),
                OrderTaxInterface::CODE => $tax->getCode(),
                OrderTaxInterface::TITLE => $tax->getTitle(),
                OrderTaxInterface::PERCENT => $tax->getPercent(),
                OrderTaxInterface::AMOUNT => $tax->getAmount(),
                OrderTaxInterface::BASE_AMOUNT => $tax->getBaseAmount(),
                OrderTaxInterface::BASE_REAL_AMOUNT => $tax->getBaseRealAmount(),
                OrderTaxInterface::PRIORITY => $tax->getPriority(),
                OrderTaxInterface::POSITION => $tax->getPosition(),
                OrderTaxInterface::PROCESS => $tax->getProcess(),
            ]);

            $taxModel->save();
            foreach ($tax->getItems() as $taxItem) {
                /** @var Item $taxItemModel */
                $taxItemModel = $taxItemCollection->getItemById($taxItem->getTaxItemId());
                if ($taxItemModel) {
                    $taxItemModel->isDeleted(false);
                } else {
                    $taxItemModel = $this->taxItemFactory->create();
                }
                $taxItemModel->addData([
                    OrderTaxItemInterface::TAX_ID => $taxModel->getId(),
                    OrderTaxItemInterface::ITEM_ID => $taxItem->getItemId(),
                    OrderTaxItemInterface::ASSOCIATED_ITEM_ID => $taxItem->getAssociatedItemId(),
                    OrderTaxItemInterface::TAX_PERCENT => $taxItem->getTaxPercent(),
                    OrderTaxItemInterface::TAXABLE_ITEM_TYPE => $taxItem->getTaxableItemType(),
                    OrderTaxItemInterface::AMOUNT => $taxItem->getAmount(),
                    OrderTaxItemInterface::BASE_AMOUNT => $taxItem->getBaseAmount(),
                    OrderTaxItemInterface::REAL_AMOUNT => $taxItem->getRealAmount(),
                    OrderTaxItemInterface::REAL_BASE_AMOUNT => $taxItem->getRealBaseAmount(),
                ]);
                $taxItemModel->save();
            }
        }
        $taxCollection->each('save');
        $taxItemCollection->each('save');
    }
}
