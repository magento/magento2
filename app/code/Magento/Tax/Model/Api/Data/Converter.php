<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Model\Api\Data;

use Magento\Sales\Model\Order\Tax;
use Magento\Sales\Model\Order\Tax\Item;
use Magento\Sales\Model\Order\Tax\ItemFactory;
use Magento\Sales\Model\Order\TaxFactory;
use Magento\Tax\Api\Data\OrderTaxInterface;
use Magento\Tax\Api\Data\OrderTaxInterfaceFactory;
use Magento\Tax\Api\Data\OrderTaxItemInterface;
use Magento\Tax\Api\Data\OrderTaxItemInterfaceFactory;

class Converter
{
    /**
     * @param TaxFactory $taxModelFactory
     * @param ItemFactory $taxItemModelFactory
     * @param OrderTaxInterfaceFactory $orderTaxFactory
     * @param OrderTaxItemInterfaceFactory $orderTaxItemFactory
     */
    public function __construct(
        private readonly TaxFactory $taxModelFactory,
        private readonly ItemFactory $taxItemModelFactory,
        private readonly OrderTaxInterfaceFactory $orderTaxFactory,
        private readonly OrderTaxItemInterfaceFactory $orderTaxItemFactory
    ) {
    }

    /**
     * Create tax data model
     *
     * @return OrderTaxInterface
     */
    public function createTaxDataModel(): OrderTaxInterface
    {
        return $this->orderTaxFactory->create();
    }

    /**
     * Create tax item data model
     *
     * @return OrderTaxItemInterface
     */
    public function createTaxItemDataModel(): OrderTaxItemInterface
    {
        return $this->orderTaxItemFactory->create();
    }

    /**
     * Create tax model
     *
     * @return Tax
     */
    public function createTaxModel(): Tax
    {
        return $this->taxModelFactory->create();
    }

    /**
     * Create tax item model
     *
     * @return Item
     */
    public function createTaxItemModel(): Item
    {
        return $this->taxItemModelFactory->create();
    }

    /**
     * Hydrate tax data model from tax model
     *
     * @param OrderTaxInterface $taxDataModel
     * @param Tax $taxModel
     * @return void
     */
    public function hydrateTaxDataModel(OrderTaxInterface $taxDataModel, Tax $taxModel): void
    {
        $taxDataModel->setTaxId($taxModel->getData(OrderTaxInterface::TAX_ID));
        $taxDataModel->setOrderId($taxModel->getData(OrderTaxInterface::ORDER_ID));
        $taxDataModel->setCode($taxModel->getData(OrderTaxInterface::CODE));
        $taxDataModel->setTitle($taxModel->getData(OrderTaxInterface::TITLE));
        $taxDataModel->setPercent($taxModel->getData(OrderTaxInterface::PERCENT));
        $taxDataModel->setAmount($taxModel->getData(OrderTaxInterface::AMOUNT));
        $taxDataModel->setBaseAmount($taxModel->getData(OrderTaxInterface::BASE_AMOUNT));
        $taxDataModel->setBaseRealAmount($taxModel->getData(OrderTaxInterface::BASE_REAL_AMOUNT));
        $taxDataModel->setPriority($taxModel->getData(OrderTaxInterface::PRIORITY));
        $taxDataModel->setPosition($taxModel->getData(OrderTaxInterface::POSITION));
        $taxDataModel->setProcess($taxModel->getData(OrderTaxInterface::PROCESS));
    }

    /**
     * Hydrate tax item data model from tax item model
     *
     * @param OrderTaxItemInterface $taxItemDataModel
     * @param Item $taxItemModel
     * @return void
     */
    public function hydrateTaxItemDataModel(OrderTaxItemInterface $taxItemDataModel, Item $taxItemModel): void
    {
        $taxItemDataModel->setTaxItemId($taxItemModel->getData(OrderTaxItemInterface::TAX_ITEM_ID));
        $taxItemDataModel->setTaxId($taxItemModel->getData(OrderTaxItemInterface::TAX_ID));
        $taxItemDataModel->setItemId($taxItemModel->getData(OrderTaxItemInterface::ITEM_ID));
        $taxItemDataModel->setTaxPercent($taxItemModel->getData(OrderTaxItemInterface::TAX_PERCENT));
        $taxItemDataModel->setAmount($taxItemModel->getData(OrderTaxItemInterface::AMOUNT));
        $taxItemDataModel->setBaseAmount($taxItemModel->getData(OrderTaxItemInterface::BASE_AMOUNT));
        $taxItemDataModel->setRealAmount($taxItemModel->getData(OrderTaxItemInterface::REAL_AMOUNT));
        $taxItemDataModel->setRealBaseAmount($taxItemModel->getData(OrderTaxItemInterface::REAL_BASE_AMOUNT));
        $taxItemDataModel->setAssociatedItemId($taxItemModel->getData(OrderTaxItemInterface::ASSOCIATED_ITEM_ID));
        $taxItemDataModel->setTaxableItemType($taxItemModel->getData(OrderTaxItemInterface::TAXABLE_ITEM_TYPE));
    }

    /**
     *  Hydrate tax model from tax data model
     *
     * @param Tax $taxModel
     * @param OrderTaxInterface $taxDataModel
     * @return void
     */
    public function hydrateTaxModel(Tax $taxModel, OrderTaxInterface $taxDataModel): void
    {
        $taxModel->addData([
            OrderTaxInterface::ORDER_ID => $taxDataModel->getOrderId(),
            OrderTaxInterface::CODE => $taxDataModel->getCode(),
            OrderTaxInterface::TITLE => $taxDataModel->getTitle(),
            OrderTaxInterface::PERCENT => $taxDataModel->getPercent(),
            OrderTaxInterface::AMOUNT => $taxDataModel->getAmount(),
            OrderTaxInterface::BASE_AMOUNT => $taxDataModel->getBaseAmount(),
            OrderTaxInterface::BASE_REAL_AMOUNT => $taxDataModel->getBaseRealAmount(),
            OrderTaxInterface::PRIORITY => $taxDataModel->getPriority(),
            OrderTaxInterface::POSITION => $taxDataModel->getPosition(),
            OrderTaxInterface::PROCESS => $taxDataModel->getProcess(),
        ]);
    }

    /**
     * Hydrate tax item model from tax item data model
     *
     * @param Item $taxItemModel
     * @param OrderTaxItemInterface $taxItemDataModel
     * @return void
     */
    public function hydrateTaxItemModel(Item $taxItemModel, OrderTaxItemInterface $taxItemDataModel): void
    {
        $taxItemModel->addData([
            OrderTaxItemInterface::TAX_ID => $taxItemDataModel->getTaxId(),
            OrderTaxItemInterface::ITEM_ID => $taxItemDataModel->getItemId(),
            OrderTaxItemInterface::ASSOCIATED_ITEM_ID => $taxItemDataModel->getAssociatedItemId(),
            OrderTaxItemInterface::TAX_PERCENT => $taxItemDataModel->getTaxPercent(),
            OrderTaxItemInterface::TAXABLE_ITEM_TYPE => $taxItemDataModel->getTaxableItemType(),
            OrderTaxItemInterface::AMOUNT => $taxItemDataModel->getAmount(),
            OrderTaxItemInterface::BASE_AMOUNT => $taxItemDataModel->getBaseAmount(),
            OrderTaxItemInterface::REAL_AMOUNT => $taxItemDataModel->getRealAmount(),
            OrderTaxItemInterface::REAL_BASE_AMOUNT => $taxItemDataModel->getRealBaseAmount(),
        ]);
    }
}
