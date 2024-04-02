<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Model\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Tax;
use Magento\Sales\Model\ResourceModel\Order\Tax\CollectionFactory as TaxCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item\CollectionFactory as TaxItemCollectionFactory;
use Magento\Tax\Api\Data\OrderTaxInterface;
use Magento\Tax\Api\Data\OrderTaxItemInterface;
use Magento\Tax\Model\Api\Data\Converter;

/**
 * Add taxes extension attribute to order
 */
class AddTaxesExtensionAttribute
{
    /**
     * @param TaxCollectionFactory $taxCollectionFactory
     * @param TaxItemCollectionFactory $taxItemCollectionFactory
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     * @param Converter $dataConverter
     */
    public function __construct(
        private readonly TaxCollectionFactory $taxCollectionFactory,
        private readonly TaxItemCollectionFactory $taxItemCollectionFactory,
        private readonly OrderExtensionFactory $orderExtensionFactory,
        private readonly OrderItemExtensionFactory $orderItemExtensionFactory,
        private readonly Converter $dataConverter
    ) {
    }

    /**
     * Add taxes extension attribute to order
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
     * Add taxes extension attribute to orders
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
     * Add taxes items extension attribute to orders
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
            OrderTaxInterface::ORDER_ID,
            ['in' => array_map(fn (OrderInterface $order) => $order->getEntityId(), $orders)]
        );
        $taxCollection->addAttributeToSort(OrderTaxInterface::TAX_ID, 'ASC');

        $taxItemCollection = $this->taxItemCollectionFactory->create();
        $taxItemCollection->addFieldToFilter(
            OrderTaxItemInterface::TAX_ID,
            ['in' => array_map(fn (Tax $taxModel) => $taxModel->getId(), $taxCollection->getItems())]
        );
        $taxItemCollection->addAttributeToSort(OrderTaxItemInterface::TAX_ITEM_ID, 'ASC');

        foreach ($orders as $order) {
            $taxes = [];
            $additionalItemizedTaxes = [];
            $orderItemAssociatedTaxes = [];
            $taxModels = $taxCollection->getItemsByColumnValue(
                OrderTaxInterface::ORDER_ID,
                $order->getEntityId()
            );
            foreach ($taxModels as $taxModel) {
                $taxItemModels = $taxItemCollection->getItemsByColumnValue(
                    OrderTaxItemInterface::TAX_ID,
                    $taxModel->getId()
                );

                foreach ($taxItemModels as $taxItemModel) {
                    $taxItem = $this->dataConverter->createTaxItemDataModel();
                    $this->dataConverter->hydrateTaxItemDataModel($taxItem, $taxItemModel);
                    $taxItem->setTaxCode(
                        $taxModel->getData(OrderTaxInterface::CODE)
                    );
                    if ($taxItem->getAssociatedItemId() || $taxItem->getItemId()) {
                        $taxItemId = $taxItem->getItemId() ?: $taxItem->getAssociatedItemId();
                        $orderItemAssociatedTaxes[$taxItemId][] = $taxItem;
                    } else {
                        $additionalItemizedTaxes[] = $taxItem;
                    }
                }

                $tax = $this->dataConverter->createTaxDataModel();
                $this->dataConverter->hydrateTaxDataModel($tax, $taxModel);
                $taxes[] = $tax;
            }
            $this->addOrderItemsAssociatedItemizedTaxes($order, $orderItemAssociatedTaxes);
            $extensionAttributes = $order->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->orderExtensionFactory->create();
            }
            $extensionAttributes->setAdditionalItemizedTaxes($additionalItemizedTaxes);
            $extensionAttributes->setTaxes($taxes);
        }
    }

    /**
     * Remove orders that already have taxes extension attribute
     *
     * @param OrderInterface[] $orders
     * @return OrderInterface[]
     */
    private function removeProcessedOrders(array $orders): array
    {
        foreach ($orders as $index => $order) {
            if ($order->getExtensionAttributes()?->getTaxes() !== null) {
                unset($orders[$index]);
            }
        }
        return $orders;
    }

    /**
     * Add itemized taxes extension attribute to order items
     *
     * @param OrderInterface $order
     * @param OrderTaxItemInterface[][] $orderItemAssociatedTaxes
     * @return void
     */
    private function addOrderItemsAssociatedItemizedTaxes(OrderInterface $order, array $orderItemAssociatedTaxes): void
    {
        foreach ($order->getItems() as $orderItem) {
            $extensionAttributes = $orderItem->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->orderItemExtensionFactory->create();
            }
            $extensionAttributes->setItemizedTaxes($orderItemAssociatedTaxes[$orderItem->getItemId()] ?? []);
            $orderItem->setExtensionAttributes($extensionAttributes);
        }
    }
}
