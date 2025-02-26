<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Tax\Api\OrderTaxManagementInterface;

/**
 * Resolve order totals taxes and discounts for order
 */
class OrderTotal implements ResolverInterface
{

    /**
     * OrderTotal Constructor
     *
     * @param OrderTaxManagementInterface $orderTaxManagement
     */
    public function __construct(
        private readonly OrderTaxManagementInterface $orderTaxManagement,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderInterface $order */
        $order = $value['model'];
        $currency = $order->getOrderCurrencyCode();

        return [
            'base_grand_total' => [
                'value' => $order->getBaseGrandTotal(),
                'currency' => $order->getBaseCurrencyCode()
            ],
            'grand_total' => ['value' => $order->getGrandTotal(), 'currency' => $currency],
            'subtotal' => ['value' => $order->getSubtotal(), 'currency' => $currency],
            'subtotal_incl_tax' => ['value' => $order->getSubtotalInclTax(), 'currency' => $currency],
            'subtotal_excl_tax' => ['value' => $order->getSubtotal(), 'currency' => $currency],
            'total_tax' => ['value' => $order->getTaxAmount(), 'currency' => $currency],
            'taxes' => $this->getAppliedTaxesDetails($order),
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
     * @throws NoSuchEntityException
     */
    private function getAllAppliedTaxesOnOrders(OrderInterface $order): array
    {
        return array_map(
            fn($appliedTaxesData) => [
                'title' => $appliedTaxesData->getTitle(),
                'percent' => $appliedTaxesData->getPercent(),
                'amount' => $appliedTaxesData->getAmount(),
            ],
            $this->orderTaxManagement->getOrderTaxDetails($order->getEntityId())->getAppliedTaxes()
        );
    }

    /**
     * Return taxes applied to the current order
     *
     * @param OrderInterface $order
     * @return array
     * @throws NoSuchEntityException
     */
    private function getAppliedTaxesDetails(OrderInterface $order): array
    {
        return array_map(
            fn($appliedTaxes) => [
                'rate' => $appliedTaxes['percent'] ?? 0,
                'title' => $appliedTaxes['title'] ?? null,
                'amount' => [
                    'value' => $appliedTaxes['amount'] ?? 0,
                    'currency' => $order->getOrderCurrencyCode()
                ]
            ],
            $this->getAllAppliedTaxesOnOrders($order)
        );
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
                    'value' => abs((float) $order->getDiscountAmount()),
                    'currency' => $order->getOrderCurrencyCode()
                ],
                'order_model' => $order
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
                    'currency' => $order->getOrderCurrencyCode()
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
                        'value' => abs((float) $order->getShippingDiscountAmount()),
                        'currency' => $order->getOrderCurrencyCode()
                    ]
                ];
        }
        return $shippingDiscounts;
    }
}
