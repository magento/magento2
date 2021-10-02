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
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Resolve order totals taxes and discounts for order
 */
class OrderTotal implements ResolverInterface
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * OrderTotal constructor.
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(PriceCurrency $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritDoc
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
        $baseCurrency = $order->getBaseCurrencyCode();

        return [
            'base_grand_total' => ['value' => $order->getBaseGrandTotal(), 'currency' => $baseCurrency, 'formatted' => $this->priceCurrency->format($order->getBaseGrandTotal(),false,null,null,$baseCurrency)],
            'grand_total' => ['value' => $order->getGrandTotal(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($order->getGrandTotal(),false,null,null,$currency)],
            'subtotal' => ['value' => $order->getSubtotal(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($order->getSubtotal(),false,null,null,$currency)],
            'total_tax' => ['value' => $order->getTaxAmount(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($order->getTaxAmount(),false,null,null,$currency)],
            'taxes' => $this->getAppliedTaxesDetails($order),
            'discounts' => $this->getDiscountDetails($order),
            'total_shipping' => ['value' => $order->getShippingAmount(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($order->getShippingAmount(),false,null,null,$currency)],
            'shipping_handling' => [
                'amount_excluding_tax' => [
                    'value' => $order->getShippingAmount(),
                    'currency' => $order->getOrderCurrencyCode(),
                    'formatted' => $this->priceCurrency->format($order->getShippingAmount(),false,null,null,$order->getOrderCurrencyCode())
                ],
                'amount_including_tax' => [
                    'value' => $order->getShippingInclTax(),
                    'currency' => $currency,
                    'formatted' => $this->priceCurrency->format($order->getShippingInclTax(),false,null,null,$currency)
                ],
                'total_amount' => [
                    'value' => $order->getShippingAmount(),
                    'currency' => $currency,
                    'formatted' => $this->priceCurrency->format($order->getShippingAmount(),false,null,null,$currency)
                ],
                'taxes' => $this->getAppliedShippingTaxesDetails($order),
                'discounts' => $this->getShippingDiscountDetails($order),
            ],
            'model' => $order
        ];
    }

    /**
     * Retrieve applied taxes that apply to the order
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getAllAppliedTaxesOnOrders(OrderInterface $order): array
    {
        $extensionAttributes = $order->getExtensionAttributes();
        $appliedTaxes = $extensionAttributes->getAppliedTaxes() ?? [];
        $allAppliedTaxOnOrders = [];
        foreach ($appliedTaxes as $taxIndex => $appliedTaxesData) {
            $allAppliedTaxOnOrders[$taxIndex] = [
                'title' => $appliedTaxesData->getDataByKey('title'),
                'percent' => $appliedTaxesData->getDataByKey('percent'),
                'amount' => $appliedTaxesData->getDataByKey('amount'),
            ];
        }
        return $allAppliedTaxOnOrders;
    }

    /**
     * Return taxes applied to the current order
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getAppliedTaxesDetails(OrderInterface $order): array
    {
        $allAppliedTaxOnOrders = $this->getAllAppliedTaxesOnOrders($order);
        $taxes = [];
        foreach ($allAppliedTaxOnOrders as $appliedTaxes) {
            $appliedTaxesArray = [
                'rate' => $appliedTaxes['percent'] ?? 0,
                'title' => $appliedTaxes['title'] ?? null,
                'amount' => [
                    'value' => $appliedTaxes['amount'] ?? 0,
                    'currency' => $order->getOrderCurrencyCode(),
                    'formatted' => $this->priceCurrency->format($appliedTaxes['amount'] ?? 0,false,null,null,$order->getOrderCurrencyCode())
                ]
            ];
            $taxes[] = $appliedTaxesArray;
        }
        return $taxes;
    }

    /**
     * Return information about an applied discount
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getDiscountDetails(OrderInterface $order): array
    {
        $orderDiscounts = [];
        if (!($order->getDiscountDescription() === null && $order->getDiscountAmount() == 0)) {
            $orderDiscounts[] = [
                'label' => $order->getDiscountDescription() ?? __('Discount'),
                'amount' => [
                    'value' => abs($order->getDiscountAmount()),
                    'currency' => $order->getOrderCurrencyCode(),
                    'formatted' => $this->priceCurrency->format(abs($order->getDiscountAmount()),false,null,null,$order->getOrderCurrencyCode())
                ]
            ];
        }
        return $orderDiscounts;
    }

    /**
     * Retrieve applied shipping taxes on items for the orders
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getAppliedShippingTaxesForItems(OrderInterface $order): array
    {
        $extensionAttributes = $order->getExtensionAttributes();
        $itemAppliedTaxes = $extensionAttributes->getItemAppliedTaxes() ?? [];
        $appliedShippingTaxesForItems = [];
        foreach ($itemAppliedTaxes as $appliedTaxForItem) {
            if ($appliedTaxForItem->getType() === "shipping") {
                foreach ($appliedTaxForItem->getAppliedTaxes() ?? [] as $taxLineItem) {
                    $taxItemIndexTitle = $taxLineItem->getDataByKey('title');
                    $appliedShippingTaxesForItems[$taxItemIndexTitle] = [
                        'title' => $taxLineItem->getDataByKey('title'),
                        'percent' => $taxLineItem->getDataByKey('percent'),
                        'amount' => $taxLineItem->getDataByKey('amount')
                    ];
                }
            }
        }
        return $appliedShippingTaxesForItems;
    }

    /**
     * Return taxes applied to the current order
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getAppliedShippingTaxesDetails(
        OrderInterface $order
    ): array {
        $appliedShippingTaxesForItems = $this->getAppliedShippingTaxesForItems($order);
        $shippingTaxes = [];
        foreach ($appliedShippingTaxesForItems as $appliedShippingTaxes) {
            $appliedShippingTaxesArray = [
                'rate' => $appliedShippingTaxes['percent'] ?? 0,
                'title' => $appliedShippingTaxes['title'] ?? null,
                'amount' => [
                    'value' => $appliedShippingTaxes['amount'] ?? 0,
                    'currency' => $order->getOrderCurrencyCode(),
                    'formatted' => $this->priceCurrency->format($appliedShippingTaxes['amount'] ?? 0,false,null,null,$order->getOrderCurrencyCode())
                ]
            ];
            $shippingTaxes[] = $appliedShippingTaxesArray;
        }
        return $shippingTaxes;
    }

    /**
     * Return information about an applied discount
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getShippingDiscountDetails(OrderInterface $order): array
    {
        $shippingDiscounts = [];
        if (!($order->getDiscountDescription() === null && $order->getShippingDiscountAmount() == 0)) {
            $shippingDiscounts[] =
                [
                    'label' => $order->getDiscountDescription() ?? __('Discount'),
                    'amount' => [
                        'value' => abs($order->getShippingDiscountAmount()),
                        'currency' => $order->getOrderCurrencyCode(),
                        'formatted' => $this->priceCurrency->format(abs($order->getShippingDiscountAmount()),false,null,null,$order->getOrderCurrencyCode())
                    ]
                ];
        }
        return $shippingDiscounts;
    }
}
