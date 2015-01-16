<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Sales\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxDataBuilder as TaxDetailsBuilder;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface as AppliedTax;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface as Item;

class TaxManagement implements \Magento\Tax\Api\OrderTaxManagementInterface
{
    /**
     * @var \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory
     */
    protected $orderItemTaxFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Tax\Api\Data\OrderTaxDetailsDataBuilder
     */
    protected $orderTaxDetailsBuilder;

    /**
     * @var \Magento\Tax\Api\Data\OrderTaxDetailsItemDataBuilder
     */
    protected $itemBuilder;

    /**
     * @var TaxDetailsBuilder
     */
    protected $appliedTaxBuilder;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory $orderItemTaxFactory
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsDataBuilder $orderTaxDetailsBuilder
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemDataBuilder $itemBuilder
     * @param TaxDetailsBuilder $appliedTaxBuilder
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory $orderItemTaxFactory,
        \Magento\Tax\Api\Data\OrderTaxDetailsDataBuilder $orderTaxDetailsBuilder,
        \Magento\Tax\Api\Data\OrderTaxDetailsItemDataBuilder $itemBuilder,
        TaxDetailsBuilder $appliedTaxBuilder
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderItemTaxFactory = $orderItemTaxFactory;
        $this->orderTaxDetailsBuilder = $orderTaxDetailsBuilder;
        $this->itemBuilder = $itemBuilder;
        $this->appliedTaxBuilder = $appliedTaxBuilder;
    }

    /**
     * Convert applied tax from array to data object
     *
     * @param TaxDetailsBuilder $appliedTaxBuilder
     * @param array $itemAppliedTax
     * @return AppliedTax
     */
    protected function convertToAppliedTaxDataObject(
        TaxDetailsBuilder $appliedTaxBuilder,
        $itemAppliedTax
    ) {
        $appliedTaxBuilder->setCode($itemAppliedTax['code']);
        $appliedTaxBuilder->setTitle($itemAppliedTax['title']);
        $appliedTaxBuilder->setPercent($itemAppliedTax['tax_percent']);
        $appliedTaxBuilder->setAmount($itemAppliedTax['real_amount']);
        $appliedTaxBuilder->setBaseAmount($itemAppliedTax['real_base_amount']);

        return $appliedTaxBuilder->create();
    }

    /**
     * Aggregate item applied taxes to get order applied taxes
     *
     * @param TaxDetailsBuilder $appliedTaxBuilder
     * @param Item[] $items
     * @return AppliedTax[]
     */
    protected function aggregateAppliedTaxes(TaxDetailsBuilder $appliedTaxBuilder, $items)
    {
        $orderAppliedTaxes = [];
        $orderAppliedTaxesData = [];
        foreach ($items as $item) {
            $itemAppliedTaxes = $item->getAppliedTaxes();
            foreach ($itemAppliedTaxes as $itemAppliedTax) {
                $code = $itemAppliedTax->getCode();
                if (!isset($orderAppliedTaxesData[$code])) {
                    $orderAppliedTaxesData[$code] = [
                        AppliedTax::KEY_CODE => $code,
                        AppliedTax::KEY_TITLE => $itemAppliedTax->getTitle(),
                        AppliedTax::KEY_PERCENT => $itemAppliedTax->getPercent(),
                        AppliedTax::KEY_AMOUNT => $itemAppliedTax->getAmount(),
                        AppliedTax::KEY_BASE_AMOUNT => $itemAppliedTax->getBaseAmount(),
                    ];
                } else {
                    $orderAppliedTaxesData[$code][AppliedTax::KEY_AMOUNT] += $itemAppliedTax->getAmount();
                    $orderAppliedTaxesData[$code][AppliedTax::KEY_BASE_AMOUNT] += $itemAppliedTax->getBaseAmount();
                }
            }
        }
        foreach ($orderAppliedTaxesData as $orderAppliedTaxData) {
            $appliedTaxBuilder->setCode($orderAppliedTaxData[AppliedTax::KEY_CODE]);
            $appliedTaxBuilder->setTitle($orderAppliedTaxData[AppliedTax::KEY_TITLE]);
            $appliedTaxBuilder->setPercent($orderAppliedTaxData[AppliedTax::KEY_PERCENT]);
            $appliedTaxBuilder->setAmount($orderAppliedTaxData[AppliedTax::KEY_AMOUNT]);
            $appliedTaxBuilder->setBaseAmount($orderAppliedTaxData[AppliedTax::KEY_BASE_AMOUNT]);
            $orderAppliedTaxes[] = $appliedTaxBuilder->create();
        }
        return $orderAppliedTaxes;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderTaxDetails($orderId)
    {
        $order = $this->orderFactory->create()->load($orderId);
        if (!$order) {
            throw new NoSuchEntityException(
                NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                [
                    'fieldName' => 'orderId',
                    'fieldValue' => $orderId,
                ]
            );
        }

        $orderItemAppliedTaxes = $this->orderItemTaxFactory->create()->getTaxItemsByOrderId($orderId);
        $itemsData = [];
        foreach ($orderItemAppliedTaxes as $itemAppliedTax) {
            //group applied taxes by item
            if (isset($itemAppliedTax['item_id'])) {
                //The taxable is a product
                $itemId = $itemAppliedTax['item_id'];
                if (!isset($itemsData[$itemId])) {
                    $itemsData[$itemId] = [
                        Item::KEY_ITEM_ID => $itemAppliedTax['item_id'],
                        Item::KEY_TYPE => $itemAppliedTax['taxable_item_type'],
                        Item::KEY_ASSOCIATED_ITEM_ID => null,
                    ];
                }
                $itemsData[$itemId]['applied_taxes'][$itemAppliedTax['code']] =
                    $this->convertToAppliedTaxDataObject($this->appliedTaxBuilder, $itemAppliedTax);
            } elseif (isset($itemAppliedTax['associated_item_id'])) {
                //The taxable is associated with a product, e.g., weee, gift wrapping etc.
                $itemId = $itemAppliedTax['associated_item_id'];
                $key = $itemAppliedTax['taxable_item_type'] . $itemId;
                if (!isset($itemsData[$key])) {
                    $itemsData[$key] = [
                        Item::KEY_ITEM_ID => null,
                        Item::KEY_TYPE => $itemAppliedTax['taxable_item_type'],
                        Item::KEY_ASSOCIATED_ITEM_ID => $itemId,
                    ];
                }
                $itemsData[$key]['applied_taxes'][$itemAppliedTax['code']] =
                    $this->convertToAppliedTaxDataObject($this->appliedTaxBuilder, $itemAppliedTax);
            } else {
                //The taxable is not associated with a product, e.g., shipping
                //Use item type as key
                $key = $itemAppliedTax['taxable_item_type'];
                if (!isset($itemsData[$key])) {
                    $itemsData[$key] = [
                        Item::KEY_TYPE => $itemAppliedTax['taxable_item_type'],
                        Item::KEY_ITEM_ID => null,
                        Item::KEY_ASSOCIATED_ITEM_ID => null,
                    ];
                }
                $itemsData[$key][Item::KEY_APPLIED_TAXES][$itemAppliedTax['code']] =
                    $this->convertToAppliedTaxDataObject($this->appliedTaxBuilder, $itemAppliedTax);
            }
        }

        $items = [];
        foreach ($itemsData as $itemData) {
            $this->itemBuilder->setType($itemData[Item::KEY_TYPE]);
            $this->itemBuilder->setItemId($itemData[Item::KEY_ITEM_ID]);
            $this->itemBuilder->setAssociatedItemId($itemData[Item::KEY_ASSOCIATED_ITEM_ID]);
            $this->itemBuilder->setAppliedTaxes($itemData[Item::KEY_APPLIED_TAXES]);
            $items[] = $this->itemBuilder->create();
        }
        $this->orderTaxDetailsBuilder->setItems($items);
        $orderAppliedTaxesDOs = $this->aggregateAppliedTaxes($this->appliedTaxBuilder, $items);
        $this->orderTaxDetailsBuilder->setAppliedTaxes($orderAppliedTaxesDOs);
        return $this->orderTaxDetailsBuilder->create();
    }
}
