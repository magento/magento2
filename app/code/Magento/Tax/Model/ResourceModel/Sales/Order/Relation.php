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
use Magento\Tax\Api\Data\OrderTaxItemInterface;

/**
 * Saves order applied taxes
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
        if (!$this->shouldProcess($object)) {
            return;
        }

        $taxCollection = $this->taxCollectionFactory->create();
        $taxCollection->addFieldToFilter(
            'order_id',
            ['eq' => $object->getEntityId()]
        );

        $taxItemCollection = $this->taxItemCollectionFactory->create();
        $taxItemCollection->addFieldToFilter(
            OrderTaxItemInterface::TAX_ID,
            ['in' => array_map(fn (Tax $taxModel) => $taxModel->getId(), $taxCollection->getItems())]
        );

        $taxCollection->each('isDeleted', [true]);
        $taxItemCollection->each('isDeleted', [true]);

        foreach ($object->getExtensionAttributes()->getAppliedTaxes() as $appliedTax) {
            /** @var Tax $taxModel */
            $taxModel = $taxCollection->getItemByColumnValue('code', $appliedTax->getCode());
            if ($taxModel) {
                $taxModel->isDeleted(false);
            } else {
                $taxModel = $this->taxFactory->create();
                $taxModel->setPriority(0);
                $taxModel->setPosition(0);
                $taxModel->setProcess(0);
                $taxModel->setOrderId($object->getEntityId());
            }

            $taxModel->setCode($appliedTax->getCode());
            $taxModel->setTitle($appliedTax->getTitle());
            $taxModel->setPercent($appliedTax->getPercent());
            if ($appliedTax->getExtensionAttributes()?->getItems() !== null) {
                $this->aggregate($taxModel, $appliedTax->getExtensionAttributes()->getItems());
                $taxModel->save();
                foreach ($appliedTax->getExtensionAttributes()->getItems() as $appliedTaxItem) {
                    /** @var Item $taxItemModel */
                    $taxItemModel = $taxItemCollection->getItemById($appliedTaxItem->getTaxItemId());
                    if ($taxItemModel) {
                        $taxItemModel->isDeleted(false);
                    } else {
                        $taxItemModel = $this->taxItemFactory->create();
                    }
                    $taxItemModel->addData([
                        OrderTaxItemInterface::TAX_ID => $taxModel->getId(),
                        OrderTaxItemInterface::ITEM_ID => $appliedTaxItem->getItemId(),
                        OrderTaxItemInterface::ASSOCIATED_ITEM_ID => $appliedTaxItem->getAssociatedItemId(),
                        OrderTaxItemInterface::TAX_PERCENT => $appliedTaxItem->getTaxPercent(),
                        OrderTaxItemInterface::TAXABLE_ITEM_TYPE => $appliedTaxItem->getTaxableItemType(),
                        OrderTaxItemInterface::AMOUNT => $appliedTaxItem->getAmount(),
                        OrderTaxItemInterface::BASE_AMOUNT => $appliedTaxItem->getBaseAmount(),
                        OrderTaxItemInterface::REAL_AMOUNT => $appliedTaxItem->getRealAmount(),
                        OrderTaxItemInterface::REAL_BASE_AMOUNT => $appliedTaxItem->getRealBaseAmount(),
                    ]);
                    $taxItemModel->save();
                }
            } else {
                $taxModel->save();
                foreach ($taxItemCollection->getItemsByColumnValue('tax_id', $taxModel->getId()) as $item) {
                    $item->isDeleted(false);
                }
            }
        }
        $taxCollection->each('save');
        $taxItemCollection->each('save');
    }

    /**
     * Aggregate item taxe amounts into tax model
     *
     * @param Tax $tax
     * @param OrderTaxItemInterface[] $items
     */
    private function aggregate(Tax $tax, array $items): void
    {
        $tax->setAmount(0);
        $tax->setBaseAmount(0);
        $tax->setBaseRealAmount(0);
        foreach ($items as $item) {
            $tax->setAmount($tax->getAmount() + $item->getAmount());
            $tax->setBaseAmount($tax->getBaseAmount() + $item->getBaseAmount());
            $tax->setBaseRealAmount($tax->getBaseRealAmount() + $item->getRealBaseAmount());
        }
    }

    /**
     * Check if applied taxes extension should be processed
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function shouldProcess(AbstractModel $object): bool
    {
        if (!$object instanceof Order) {
            return false;
        }

        if ($object->getExtensionAttributes()?->getConvertingFromQuote()) {
            $this->convertQuoteTaxToOrderTax->execute($object);
            return false;
        }

        return $object->getExtensionAttributes()?->getAppliedTaxes() !== null;
    }
}
