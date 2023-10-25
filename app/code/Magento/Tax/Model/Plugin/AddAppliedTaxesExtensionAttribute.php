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

namespace Magento\Tax\Model\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Tax;
use Magento\Sales\Model\ResourceModel\Order\Tax\CollectionFactory as TaxCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item\CollectionFactory as TaxItemCollectionFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionFactory;
use Magento\Tax\Api\Data\OrderTaxItemInterface;
use Magento\Tax\Api\Data\OrderTaxItemInterfaceFactory;

/**
 * Add applied taxes extension attribute to order
 */
class AddAppliedTaxesExtensionAttribute
{
    /**
     * @param TaxCollectionFactory $taxCollectionFactory
     * @param TaxItemCollectionFactory $taxItemCollectionFactory
     * @param OrderTaxItemInterfaceFactory $orderTaxItemFactory
     * @param OrderTaxDetailsAppliedTaxExtensionFactory $orderTaxDetailsAppliedTaxExtensionFactory
     */
    public function __construct(
        private readonly TaxCollectionFactory $taxCollectionFactory,
        private readonly TaxItemCollectionFactory $taxItemCollectionFactory,
        private readonly OrderTaxItemInterfaceFactory $orderTaxItemFactory,
        private readonly OrderTaxDetailsAppliedTaxExtensionFactory $orderTaxDetailsAppliedTaxExtensionFactory
    ) {
    }

    /**
     * Add applied taxes extension attribute to order
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $entity
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $entity
    ): OrderInterface {
        $this->execute([$entity]);
        return $entity;
    }

    /**
     * Add applied taxes extension attribute to orders
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ): OrderSearchResultInterface {
        $this->execute($searchResult->getItems());
        return $searchResult;
    }

    /**
     * Add applied taxes items extension attribute to orders
     *
     * @param OrderInterface[] $orders
     * @return void
     */
    private function execute(array $orders): void
    {
        $orders = $this->removeProcessedOrders($orders);

        if (empty($orders)) {
            return;
        }

        $taxCollection = $this->taxCollectionFactory->create();
        $taxCollection->addFieldToFilter(
            'order_id',
            ['in' => array_map(fn (OrderInterface $order) => $order->getEntityId(), $orders)]
        );

        $taxItemCollection = $this->taxItemCollectionFactory->create();
        $taxItemCollection->addFieldToFilter(
            OrderTaxItemInterface::TAX_ID,
            ['in' => array_map(fn (Tax $taxModel) => $taxModel->getId(), $taxCollection->getItems())]
        );
        foreach ($orders as $order) {
            $orderTaxModels = $taxCollection->getItemsByColumnValue('order_id', $order->getEntityId());
            foreach ($order->getExtensionAttributes()->getAppliedTaxes() as $appliedTax) {
                $taxModels = array_filter(
                    $orderTaxModels,
                    fn (Tax $taxModel) => $taxModel->getCode() === $appliedTax->getCode()
                );
                if (count($taxModels) !== 1) {
                    continue;
                }
                $taxModel = reset($taxModels);
                $extensionAttributes = $appliedTax->getExtensionAttributes();
                if (!$extensionAttributes) {
                    $extensionAttributes = $this->orderTaxDetailsAppliedTaxExtensionFactory->create();
                }
                $items = [];

                $taxItemModels = $taxItemCollection->getItemsByColumnValue(
                    OrderTaxItemInterface::TAX_ID,
                    $taxModel->getId()
                );

                foreach ($taxItemModels as $taxItemModel) {
                    /** @var OrderTaxItemInterface $item */
                    $item = $this->orderTaxItemFactory->create();
                    $item->setTaxItemId($taxItemModel->getData(OrderTaxItemInterface::TAX_ITEM_ID));
                    $item->setTaxId($taxItemModel->getData(OrderTaxItemInterface::TAX_ID));
                    $item->setItemId($taxItemModel->getData(OrderTaxItemInterface::ITEM_ID));
                    $item->setTaxPercent($taxItemModel->getData(OrderTaxItemInterface::TAX_PERCENT));
                    $item->setAmount($taxItemModel->getData(OrderTaxItemInterface::AMOUNT));
                    $item->setBaseAmount($taxItemModel->getData(OrderTaxItemInterface::BASE_AMOUNT));
                    $item->setRealAmount($taxItemModel->getData(OrderTaxItemInterface::REAL_AMOUNT));
                    $item->setRealBaseAmount($taxItemModel->getData(OrderTaxItemInterface::REAL_BASE_AMOUNT));
                    $item->setAssociatedItemId($taxItemModel->getData(OrderTaxItemInterface::ASSOCIATED_ITEM_ID));
                    $item->setTaxableItemType($taxItemModel->getData(OrderTaxItemInterface::TAXABLE_ITEM_TYPE));
                    $items[] = $item;
                }

                $extensionAttributes->setItems($items);
                $appliedTax->setExtensionAttributes($extensionAttributes);
            }
        }
    }

    /**
     * Remove orders that already have applied taxes extension attribute
     *
     * @param OrderInterface[] $orders
     * @return OrderInterface[]
     */
    private function removeProcessedOrders(array $orders): array
    {
        foreach ($orders as $index => $order) {
            $processed = true;
            foreach ($order->getExtensionAttributes()?->getAppliedTaxes() ?? [] as $appliedTax) {
                if ($appliedTax->getExtensionAttributes()?->getItems() === null) {
                    $processed = false;
                    break;
                }
            }
            if ($processed) {
                unset($orders[$index]);
            }
        }
        return $orders;
    }
}
