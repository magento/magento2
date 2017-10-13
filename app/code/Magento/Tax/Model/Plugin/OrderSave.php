<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Plugin;

/**
 * Class \Magento\Tax\Model\Plugin\OrderSave
 *
 */
class OrderSave
{
    /**
     * @var \Magento\Tax\Model\Sales\Order\TaxFactory
     */
    protected $orderTaxFactory;

    /**
     * @var \Magento\Sales\Model\Order\Tax\ItemFactory
     */
    protected $taxItemFactory;

    /**
     * @param \Magento\Tax\Model\Sales\Order\TaxFactory $orderTaxFactory
     * @param \Magento\Sales\Model\Order\Tax\ItemFactory $taxItemFactory
     */
    public function __construct(
        \Magento\Tax\Model\Sales\Order\TaxFactory $orderTaxFactory,
        \Magento\Sales\Model\Order\Tax\ItemFactory $taxItemFactory
    ) {
        $this->orderTaxFactory = $orderTaxFactory;
        $this->taxItemFactory = $taxItemFactory;
    }

    /**
     * Save order tax
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->saveOrderTax($order);
        return $order;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function saveOrderTax(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $extensionAttribute = $order->getExtensionAttributes();
        if (!$extensionAttribute ||
            !$extensionAttribute->getConvertingFromQuote() ||
            $order->getAppliedTaxIsSaved()) {
            return;
        }

        /** @var \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface[]|null $taxes */
        $taxes = $extensionAttribute->getAppliedTaxes();
        if ($taxes == null) {
            $taxes = [];
        }

        /** @var \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface[]|null $taxesForItems */
        $taxesForItems = $extensionAttribute->getItemAppliedTaxes();
        if ($taxesForItems == null) {
            $taxesForItems = [];
        }

        $ratesIdQuoteItemId = [];
        foreach ($taxesForItems as $taxesArray) {
            foreach ($taxesArray['applied_taxes'] as $rates) {
                if (isset($rates['extension_attributes'])) {
                    /** @var \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $taxRates */
                    $taxRates = $rates['extension_attributes']->getRates();
                    if (is_array($taxRates)) {
                        if (count($taxRates) == 1) {
                            $ratesIdQuoteItemId[$rates['id']][] = [
                                'id' => $taxesArray['item_id'],
                                'percent' => $rates['percent'],
                                'code' => $taxRates[0]['code'],
                                'associated_item_id' => $taxesArray['associated_item_id'],
                                'item_type' => $taxesArray['type'],
                                'amount' => $rates['amount'],
                                'base_amount' => $rates['base_amount'],
                                'real_amount' => $rates['amount'],
                                'real_base_amount' => $rates['base_amount'],
                            ];
                        } else {
                            $percentSum = 0;
                            foreach ($taxRates as $rate) {
                                $realAmount = $rates['amount'] * $rate['percent'] / $rates['percent'];
                                $realBaseAmount = $rates['base_amount'] * $rate['percent'] / $rates['percent'];
                                $ratesIdQuoteItemId[$rates['id']][] = [
                                    'id' => $taxesArray['item_id'],
                                    'percent' => $rate['percent'],
                                    'code' => $rate['code'],
                                    'associated_item_id' => $taxesArray['associated_item_id'],
                                    'item_type' => $taxesArray['type'],
                                    'amount' => $rates['amount'],
                                    'base_amount' => $rates['base_amount'],
                                    'real_amount' => $realAmount,
                                    'real_base_amount' => $realBaseAmount,
                                ];
                                $percentSum += $rate['percent'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($taxes as $row) {
            $id = $row['id'];
            if (isset($row['extension_attributes'])) {
                /** @var \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $taxRates */
                $taxRates = $row['extension_attributes']->getRates();
                if (is_array($taxRates)) {
                    foreach ($taxRates as $tax) {
                        if ($row['percent'] == null) {
                            $baseRealAmount = $row['base_amount'];
                        } else {
                            if ($row['percent'] == 0 || $tax['percent'] == 0) {
                                continue;
                            }
                            $baseRealAmount = $row['base_amount'] / $row['percent'] * $tax['percent'];
                        }
                        $hidden = isset($row['hidden']) ? $row['hidden'] : 0;
                        $priority = isset($tax['priority']) ? $tax['priority'] : 0;
                        $position = isset($tax['position']) ? $tax['position'] : 0;
                        $process = isset($row['process']) ? $row['process'] : 0;
                        $data = [
                            'order_id' => $order->getEntityId(),
                            'code' => $tax['code'],
                            'title' => $tax['title'],
                            'hidden' => $hidden,
                            'percent' => $tax['percent'],
                            'priority' => $priority,
                            'position' => $position,
                            'amount' => $row['amount'],
                            'base_amount' => $row['base_amount'],
                            'process' => $process,
                            'base_real_amount' => $baseRealAmount,
                        ];

                        /** @var $orderTax \Magento\Tax\Model\Sales\Order\Tax */
                        $orderTax = $this->orderTaxFactory->create();
                        $result = $orderTax->setData($data)->save();

                        if (isset($ratesIdQuoteItemId[$id])) {
                            foreach ($ratesIdQuoteItemId[$id] as $quoteItemId) {
                                if ($quoteItemId['code'] === $tax['code']) {
                                    $itemId = null;
                                    $associatedItemId = null;
                                    if (isset($quoteItemId['id'])) {
                                        //This is a product item
                                        $item = $order->getItemByQuoteItemId($quoteItemId['id']);
                                        if ($item !== null && $item->getId()) {
                                            $itemId = $item->getId();
                                        }
                                    } elseif (isset($quoteItemId['associated_item_id'])) {
                                        //This item is associated with a product item
                                        $item = $order->getItemByQuoteItemId($quoteItemId['associated_item_id']);
                                        $associatedItemId = $item->getId();
                                    }

                                    $data = [
                                        'item_id' => $itemId,
                                        'tax_id' => $result->getTaxId(),
                                        'tax_percent' => $quoteItemId['percent'],
                                        'associated_item_id' => $associatedItemId,
                                        'amount' => $quoteItemId['amount'],
                                        'base_amount' => $quoteItemId['base_amount'],
                                        'real_amount' => $quoteItemId['real_amount'],
                                        'real_base_amount' => $quoteItemId['real_base_amount'],
                                        'taxable_item_type' => $quoteItemId['item_type'],
                                    ];
                                    /** @var $taxItem \Magento\Sales\Model\Order\Tax\Item */
                                    $taxItem = $this->taxItemFactory->create();
                                    $taxItem->setData($data)->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        $order->setAppliedTaxIsSaved(true);
        return $this;
    }
}
