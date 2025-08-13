<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Sales\Order;

use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterfaceFactory as TaxDetailsDataObjectFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface as AppliedTax;
use Magento\Sales\Model\Order\Tax\Item;

class TaxManagement implements \Magento\Tax\Api\OrderTaxManagementInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory
     */
    protected $orderItemTaxFactory;

    /**
     * @var \Magento\Tax\Api\Data\OrderTaxDetailsInterfaceFactory
     */
    protected $orderTaxDetailsDataObjectFactory;

    /**
     * @var \Magento\Tax\Api\Data\OrderTaxDetailsItemInterfaceFactory
     */
    protected $itemDataObjectFactory;

    /**
     * @var TaxDetailsDataObjectFactory
     */
    protected $appliedTaxDataObjectFactory;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory $orderItemTaxFactory
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsInterfaceFactory $orderTaxDetailsDataObjectFactory
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param TaxDetailsDataObjectFactory $appliedTaxDataObjectFactory
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory $orderItemTaxFactory,
        \Magento\Tax\Api\Data\OrderTaxDetailsInterfaceFactory $orderTaxDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\OrderTaxDetailsItemInterfaceFactory $itemDataObjectFactory,
        TaxDetailsDataObjectFactory $appliedTaxDataObjectFactory
    ) {
        $this->orderItemTaxFactory = $orderItemTaxFactory;
        $this->orderTaxDetailsDataObjectFactory = $orderTaxDetailsDataObjectFactory;
        $this->itemDataObjectFactory = $itemDataObjectFactory;
        $this->appliedTaxDataObjectFactory = $appliedTaxDataObjectFactory;
    }

    /**
     * Convert applied tax from array to data object
     *
     * @param TaxDetailsDataObjectFactory $appliedTaxDataObjectFactory
     * @param array $itemAppliedTax
     * @param AppliedTax $existingAppliedTax
     * @return AppliedTax
     */
    protected function convertToAppliedTaxDataObject(
        TaxDetailsDataObjectFactory $appliedTaxDataObjectFactory,
        $itemAppliedTax,
        ?AppliedTax $existingAppliedTax = null
    ) {
        // if there is an existingAppliedTax, include its amount and baseAmount
        $amount = $baseAmount = 0;
        if ($existingAppliedTax !== null) {
            $amount = $existingAppliedTax->getAmount();
            $baseAmount = $existingAppliedTax->getBaseAmount();
        }

        return $appliedTaxDataObjectFactory->create()
            ->setCode($itemAppliedTax['code'])
            ->setTitle($itemAppliedTax['title'])
            ->setPercent($itemAppliedTax['tax_percent'])
            ->setAmount($itemAppliedTax['real_amount'] + $amount)
            ->setBaseAmount($itemAppliedTax['real_base_amount'] + $baseAmount);
    }

    /**
     * Aggregate item applied taxes to get order applied taxes
     *
     * @param TaxDetailsDataObjectFactory $appliedTaxDataObjectFactory
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface[] $items
     * @return AppliedTax[]
     */
    protected function aggregateAppliedTaxes(TaxDetailsDataObjectFactory $appliedTaxDataObjectFactory, $items)
    {
        $orderAppliedTaxes = [];
        $orderAppliedTaxesData = [];
        foreach ($items as $item) {
            $itemAppliedTaxes = $item->getAppliedTaxes();
            foreach ($itemAppliedTaxes as $itemAppliedTax) {
                $code = $itemAppliedTax->getCode();
                if (!isset($orderAppliedTaxesData[$code])) {
                    $orderAppliedTaxesData[$code] = [
                        Tax::KEY_CODE => $code,
                        Tax::KEY_TITLE => $itemAppliedTax->getTitle(),
                        Tax::KEY_PERCENT => $itemAppliedTax->getPercent(),
                        Tax::KEY_AMOUNT => $itemAppliedTax->getAmount(),
                        Tax::KEY_BASE_AMOUNT => $itemAppliedTax->getBaseAmount(),
                    ];
                } else {
                    $orderAppliedTaxesData[$code][Tax::KEY_AMOUNT] += $itemAppliedTax->getAmount();
                    $orderAppliedTaxesData[$code][Tax::KEY_BASE_AMOUNT] += $itemAppliedTax->getBaseAmount();
                }
            }
        }
        foreach ($orderAppliedTaxesData as $orderAppliedTaxData) {
            $orderAppliedTaxes[] = $appliedTaxDataObjectFactory->create()
                ->setCode($orderAppliedTaxData[Tax::KEY_CODE])
                ->setTitle($orderAppliedTaxData[Tax::KEY_TITLE])
                ->setPercent($orderAppliedTaxData[Tax::KEY_PERCENT])
                ->setAmount($orderAppliedTaxData[Tax::KEY_AMOUNT])
                ->setBaseAmount($orderAppliedTaxData[Tax::KEY_BASE_AMOUNT]);
        }
        return $orderAppliedTaxes;
    }

    /**
     * @inheritdoc
     */
    public function getOrderTaxDetails($orderId)
    {
        $orderItemAppliedTaxes = $this->orderItemTaxFactory->create()->getTaxItemsByOrderId($orderId);
        $itemsData = [];
        foreach ($orderItemAppliedTaxes as $itemAppliedTax) {
            $key = $itemId = $associatedItemId = null;

            //group applied taxes by item
            if (isset($itemAppliedTax['item_id'])) {
                //The taxable is a product
                //Note: the associatedItemId is null
                $itemId = $itemAppliedTax['item_id'];
                $key = $itemId;
            } elseif (isset($itemAppliedTax['associated_item_id'])) {
                //The taxable is associated with a product, e.g., weee, gift wrapping, etc.
                //Note: the itemId is null
                $associatedItemId = $itemAppliedTax['associated_item_id'];
                $key = $itemAppliedTax['taxable_item_type'] . $associatedItemId;
            } else {
                //The taxable is not associated with a product, e.g., shipping
                //Use item type as key.  Both the itemId and associatedItemId are null
                $key = $itemAppliedTax['taxable_item_type'];
            }

            // create the itemsData entry
            if (!isset($itemsData[$key])) {
                $itemsData[$key] = [
                    Item::KEY_TYPE => $itemAppliedTax['taxable_item_type'],
                    Item::KEY_ITEM_ID => $itemId,                       // might be null
                    Item::KEY_ASSOCIATED_ITEM_ID => $associatedItemId,  // might be null
                ];
            }
            $current = null;
            if (isset($itemsData[$key][Item::KEY_APPLIED_TAXES][$itemAppliedTax['code']])) {
                $current = $itemsData[$key][Item::KEY_APPLIED_TAXES][$itemAppliedTax['code']];
            }
            $itemsData[$key][Item::KEY_APPLIED_TAXES][$itemAppliedTax['code']] =
                $this->convertToAppliedTaxDataObject($this->appliedTaxDataObjectFactory, $itemAppliedTax, $current);
        }

        $items = [];
        foreach ($itemsData as $itemData) {
            $items[] = $this->itemDataObjectFactory->create()
                ->setType($itemData[Item::KEY_TYPE])
                ->setItemId($itemData[Item::KEY_ITEM_ID])
                ->setAssociatedItemId($itemData[Item::KEY_ASSOCIATED_ITEM_ID])
                ->setAppliedTaxes($itemData[Item::KEY_APPLIED_TAXES]);
        }
        $orderAppliedTaxesDOs = $this->aggregateAppliedTaxes($this->appliedTaxDataObjectFactory, $items);
        return $this->orderTaxDetailsDataObjectFactory->create()
            ->setItems($items)
            ->setAppliedTaxes($orderAppliedTaxesDOs);
    }
}
