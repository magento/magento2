<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Sales\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterfaceFactory as TaxDetailsDataObjectFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface as AppliedTax;
use Magento\Tax\Model\Sales\Order\Tax;
use Magento\Sales\Model\Order\Tax\Item;

class TaxManagement implements \Magento\Tax\Api\OrderTaxManagementInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory
     */
    protected $orderItemTaxFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

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
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory $orderItemTaxFactory
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsInterfaceFactory $orderTaxDetailsDataObjectFactory
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param TaxDetailsDataObjectFactory $appliedTaxDataObjectFactory
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory $orderItemTaxFactory,
        \Magento\Tax\Api\Data\OrderTaxDetailsInterfaceFactory $orderTaxDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\OrderTaxDetailsItemInterfaceFactory $itemDataObjectFactory,
        TaxDetailsDataObjectFactory $appliedTaxDataObjectFactory
    ) {
        $this->orderFactory = $orderFactory;
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
     * @return AppliedTax
     */
    protected function convertToAppliedTaxDataObject(
        TaxDetailsDataObjectFactory $appliedTaxDataObjectFactory,
        $itemAppliedTax
    ) {
        return $appliedTaxDataObjectFactory->create()
            ->setCode($itemAppliedTax['code'])
            ->setTitle($itemAppliedTax['title'])
            ->setPercent($itemAppliedTax['tax_percent'])
            ->setAmount($itemAppliedTax['real_amount'])
            ->setBaseAmount($itemAppliedTax['real_base_amount']);
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
     * {@inheritdoc}
     */
    public function getOrderTaxDetails($orderId)
    {
        $order = $this->orderFactory->create()->load($orderId);
        if (!$order) {
            throw new NoSuchEntityException(
                __(
                    NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                    [
                        'fieldName' => 'orderId',
                        'fieldValue' => $orderId,
                    ]
                )
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
                    $this->convertToAppliedTaxDataObject($this->appliedTaxDataObjectFactory, $itemAppliedTax);
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
                    $this->convertToAppliedTaxDataObject($this->appliedTaxDataObjectFactory, $itemAppliedTax);
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
                    $this->convertToAppliedTaxDataObject($this->appliedTaxDataObjectFactory, $itemAppliedTax);
            }
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
