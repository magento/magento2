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

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Tax;
use Magento\Sales\Model\ResourceModel\Order\Tax\CollectionFactory as TaxCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item\CollectionFactory as TaxItemCollectionFactory;
use Magento\Tax\Api\Data\OrderTaxInterface;
use Magento\Tax\Api\Data\OrderTaxItemInterface;
use Magento\Tax\Api\Data\OrderTaxInterfaceFactory;
use Magento\Tax\Api\Data\OrderTaxItemInterfaceFactory;

/**
 * Add taxes extension attribute to order
 */
class AddTaxesExtensionAttribute
{
    /**
     * @param TaxCollectionFactory $taxCollectionFactory
     * @param TaxItemCollectionFactory $taxItemCollectionFactory
     * @param OrderTaxInterfaceFactory $orderTaxFactory
     * @param OrderTaxItemInterfaceFactory $orderTaxItemFactory
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        private readonly TaxCollectionFactory $taxCollectionFactory,
        private readonly TaxItemCollectionFactory $taxItemCollectionFactory,
        private readonly OrderTaxInterfaceFactory $orderTaxFactory,
        private readonly OrderTaxItemInterfaceFactory $orderTaxItemFactory,
        private readonly OrderExtensionFactory $orderExtensionFactory
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
        foreach ($orders as $index => $order) {
            if ($order->getExtensionAttributes()?->getTaxes() !== null) {
                unset($orders[$index]);
            }
        }

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
            $taxModels = $taxCollection->getItemsByColumnValue(
                OrderTaxInterface::ORDER_ID,
                $order->getEntityId()
            );
            foreach ($taxModels as $taxModel) {
                $taxItemModels = $taxItemCollection->getItemsByColumnValue(
                    OrderTaxItemInterface::TAX_ID,
                    $taxModel->getId()
                );
                $items = [];

                foreach ($taxItemModels as $taxItemModel) {
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

                $tax = $this->orderTaxFactory->create();
                $tax->setTaxId($taxModel->getData(OrderTaxInterface::TAX_ID));
                $tax->setOrderId($taxModel->getData(OrderTaxInterface::ORDER_ID));
                $tax->setCode($taxModel->getData(OrderTaxInterface::CODE));
                $tax->setTitle($taxModel->getData(OrderTaxInterface::TITLE));
                $tax->setPercent($taxModel->getData(OrderTaxInterface::PERCENT));
                $tax->setAmount($taxModel->getData(OrderTaxInterface::AMOUNT));
                $tax->setBaseAmount($taxModel->getData(OrderTaxInterface::BASE_AMOUNT));
                $tax->setBaseRealAmount($taxModel->getData(OrderTaxInterface::BASE_REAL_AMOUNT));
                $tax->setPriority($taxModel->getData(OrderTaxInterface::PRIORITY));
                $tax->setPosition($taxModel->getData(OrderTaxInterface::POSITION));
                $tax->setProcess($taxModel->getData(OrderTaxInterface::PROCESS));
                $tax->setItems($items);
                $taxes[] = $tax;
            }
            $extensionAttributes = $order->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->orderExtensionFactory->create();
            }
            $extensionAttributes->setTaxes($taxes);
        }
    }
}
