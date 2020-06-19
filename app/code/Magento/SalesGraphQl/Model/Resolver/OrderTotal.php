<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;

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
        if (!(($value['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderInterface $order */
        $order = $value['model'];
        $currency = $order->getOrderCurrencyCode();
        $extensionAttributes = $order->getExtensionAttributes();

        $allAppliedTaxesForItemsData =  $this->getAllAppliedTaxesForItems(
            $extensionAttributes->getItemAppliedTaxes() ?? []
        );
        $appliedShippingTaxesForItemsData = $this->getAppliedShippingTaxesForItems(
            $extensionAttributes->getItemAppliedTaxes() ?? []
        );

        return [
            'base_grand_total' => ['value' => $order->getBaseGrandTotal(), 'currency' => $currency],
            'grand_total' => ['value' => $order->getGrandTotal(), 'currency' => $currency],
            'subtotal' => ['value' => $order->getSubtotal(), 'currency' => $currency],
            'total_tax' => ['value' => $order->getTaxAmount(), 'currency' => $currency],
            'taxes' => $this->getAppliedTaxesDetails($order, $allAppliedTaxesForItemsData),
            'discounts' => $this->getDiscountDetails($order),
            'total_shipping' => ['value' => $order->getShippingAmount(), 'currency' => $currency],
            'shipping_handling' => [
                'amount_excluding_tax' => [
                    'value' => $order->getShippingAmount(),
                    'currency' => $order->getOrderCurrencyCode()
                ],
                'amount_including_tax' => [
                    'value' => $order->getShippingInclTax(),
                    'currency' => $currency
                ],
                'total_amount' => [
                    'value' => $order->getShippingAmount(),
                    'currency' => $currency
                ],
                'taxes' => $this->getAppliedTaxesDetails($order, $appliedShippingTaxesForItemsData),
                'discounts' => $this->getShippingDiscountDetails($order),
            ]
        ];
    }

    /**
     * Retrieve applied taxes that apply to items
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface[] $itemAppliedTaxes
     * @return array
     */
    private function getAllAppliedTaxesForItems(array $itemAppliedTaxes): array
    {
        $allAppliedTaxesForItemsData = [];
        foreach ($itemAppliedTaxes as $taxItemIndex => $appliedTaxForItem) {
            foreach ($appliedTaxForItem->getAppliedTaxes() ?? [] as $taxLineItem) {
                $allAppliedTaxesForItemsData[$taxItemIndex][$taxItemIndex] = [
                    'title' => $taxLineItem->getDataByKey('title'),
                    'percent' => $taxLineItem->getDataByKey('percent'),
                    'amount' => $taxLineItem->getDataByKey('amount'),
                ];
            }
        }
        return $allAppliedTaxesForItemsData;
    }

    /**
     * Retrieve applied taxes that apply to shipping
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface $extensionAttributes
     * @return array
     */
    private function getAppliedShippingTaxesForItems(array $itemAppliedTaxes): array
    {
        $appliedShippingTaxesForItemsData = [];
        foreach ($itemAppliedTaxes as $taxItemIndex => $appliedTaxForItem) {
            foreach ($appliedTaxForItem->getAppliedTaxes() ?? [] as $taxLineItem) {
                if ($appliedTaxForItem->getType() === "shipping") {
                    $appliedShippingTaxesForItemsData[$taxItemIndex][$taxItemIndex] = [
                        'title' => $taxLineItem->getDataByKey('title'),
                        'percent' => $taxLineItem->getDataByKey('percent'),
                        'amount' => $taxLineItem->getDataByKey('amount')
                    ];
                }
            }
        }
        return $appliedShippingTaxesForItemsData;
    }

    /**
     * Return information about an applied discount
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getShippingDiscountDetails(OrderInterface $order)
    {
        $shippingDiscounts = [];
        if (!($order->getDiscountDescription() === null && $order->getShippingDiscountAmount() == 0)) {
            $shippingDiscounts[] =
                [
                    'label' => $order->getDiscountDescription() ?? "null",
                    'amount' => [
                        'value' => $order->getShippingDiscountAmount(),
                        'currency' => $order->getOrderCurrencyCode()
                    ]
                ];
        }
        return $shippingDiscounts;
    }

    /**
     * Return information about an applied discount
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getDiscountDetails(OrderInterface $order)
    {
        $discounts = [];
        if (!($order->getDiscountDescription() === null && $order->getDiscountAmount() == 0)) {
            $discounts[] = [
                'label' => $order->getDiscountDescription() ?? __("Discount"),
                'amount' => [
                    'value' => $order->getDiscountAmount(),
                    'currency' => $order->getOrderCurrencyCode()
                ]
            ];
        }
        return $discounts;
    }

    /**
     * Returns taxes applied to the current order
     *
     * @param OrderInterface $order
     * @param array $appliedTaxesArray
     * @return array
     */
    private function getAppliedTaxesDetails(OrderInterface $order, array $appliedTaxesArray): array
    {
        $taxes = [];
        foreach ($appliedTaxesArray as $appliedTaxesKeyIndex => $appliedTaxes) {
            $appliedTaxesArray = [
                'title' => $appliedTaxes[$appliedTaxesKeyIndex]['title'] ?? null,
                'amount' => [
                    'value' => $appliedTaxes[$appliedTaxesKeyIndex]['amount'] ?? 0,
                    'currency' => $order->getOrderCurrencyCode()
                ],
            ];
            if (!empty($appliedTaxes[$appliedTaxesKeyIndex])) {
                $appliedTaxesArray['rate'] = $appliedTaxes[$appliedTaxesKeyIndex]['percent'] ?? null;
            }
            $taxes[] = $appliedTaxesArray;
        }
        return $taxes;
    }
}
