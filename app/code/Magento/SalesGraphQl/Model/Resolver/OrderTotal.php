<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Model\Order;

class OrderTotal implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (!isset($value['model']) && !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Order $order */
        $order = $value['model'];
        $currency = $order->getOrderCurrencyCode();
        $extensionAttributes = $order->getExtensionAttributes();
        $appliedTaxesForItems = $extensionAttributes->getItemAppliedTaxes();
        $allAppliedTaxesForItemsData[] = [];
        $appliedShippingTaxesForItemsData[] = [];
        if (!empty($appliedTaxesForItems)) {
            foreach ($appliedTaxesForItems as $key => $appliedTaxForItem) {
                $appliedTaxType = $appliedTaxForItem->getType();
                $taxLineItems = $appliedTaxForItem->getAppliedTaxes();
                foreach ($taxLineItems as $taxLineItem) {
                    if ($appliedTaxType === "shipping") {
                        $appliedShippingTaxesForItemsData[$key]['title'] = $taxLineItem->getDataByKey('title');
                        $appliedShippingTaxesForItemsData[$key]['percent'] = $taxLineItem->getDataByKey('percent');
                        $appliedShippingTaxesForItemsData[$key]['amount'] = $taxLineItem->getDataByKey('amount');
                    }
                    $allAppliedTaxesForItemsData[$key]['title'] = $taxLineItem->getDataByKey('title');
                    $allAppliedTaxesForItemsData[$key]['percent'] = $taxLineItem->getDataByKey('percent');
                    $allAppliedTaxesForItemsData[$key]['amount'] = $taxLineItem->getDataByKey('amount');
                }
            }
        }

        $total = [
            'base_grand_total' => ['value' => $order->getBaseGrandTotal(), 'currency' => $currency],
            'grand_total' => ['value' => $order->getGrandTotal(), 'currency' => $currency],
            'subtotal' => ['value' => $order->getSubtotal(), 'currency' => $currency],
            'total_tax' => ['value' => $order->getTaxAmount(), 'currency' => $currency],
            'taxes' => $this->getAppliedTaxesDetails($order, $allAppliedTaxesForItemsData),
            'discounts' => $this->getDiscountDetails($order),
            'total_shipping' => ['value' => $order->getShippingAmount(), 'currency' => $currency],
            'shipping_handling' => [
                'amount_excluding_tax' => ['value' => $order->getShippingAmount(), 'currency' => $order->getOrderCurrencyCode()],
                'amount_including_tax' => ['value' => $order->getShippingInclTax(), 'currency' => $currency],
                'total_amount' => ['value' => $order->getBaseShippingAmount(), 'currency' => $currency],
                'taxes' => $this->getAppliedTaxesDetails($order, $appliedShippingTaxesForItemsData),
                'discounts' => $this->getShippingDiscountDetails($order),

            ]
        ];
        return $total;
    }

    /**
     * Returns information about an applied discount
     *
     * @param Order $order
     * @return array|null
     */
    private function getShippingDiscountDetails(Order $order)
    {
        if ($order->getDiscountDescription() === null && $order->getShippingDiscountAmount() == 0) {
            return null;
        }

        $shippingDiscounts [] =
            [
                'label' => $order->getDiscountDescription() ?? "null",
                'amount' => [
                    'value' => $order->getShippingDiscountAmount(),
                    'currency' => $order->getOrderCurrencyCode()
                ]
            ];
        return $shippingDiscounts;
    }

    /**
     * Returns information about an applied discount
     *
     * @param Order $order
     * @return array|null
     */
    private function getDiscountDetails(Order $order)
    {
        if ($order->getDiscountDescription() === null && $order->getDiscountAmount() == 0) {
            return null;
        }

        $discounts [] = [
            'label' => $order->getDiscountDescription() ?? "null",
            'amount' => [
                'value' => $order->getDiscountAmount(),
                'currency' => $order->getOrderCurrencyCode()
            ]
        ];
        return $discounts;
    }

    /**
     * Returns taxes applied to the current order
     *
     * @param Order $order
     * @param array $appliedTaxesArray
     * @return array|null
     */
    private function getAppliedTaxesDetails(Order $order, array $appliedTaxesArray): array
    {
        if (empty($appliedTaxesArray)) {
            $taxes [] = null;
        } else {
            foreach ($appliedTaxesArray as $appliedTaxes) {
                $taxes[] = [
                    'rate' => $appliedTaxes['percent'] ?? 0,
                    'title' => $appliedTaxes['title'] ?? " ",
                    'amount' => ['value' => $appliedTaxes['amount'] ?? 0 , 'currency' => $order->getOrderCurrencyCode()
                    ]
                ];
            }
            /** @var array $taxes */
            return $taxes;
        }
    }
}
