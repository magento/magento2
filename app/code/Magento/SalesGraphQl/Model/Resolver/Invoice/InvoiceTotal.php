<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\Invoice;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Tax\Api\OrderTaxManagementInterface;
use Magento\SalesGraphQl\Model\SalesItem\ShippingTaxCalculator;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Resolver for Invoice total
 */
class InvoiceTotal implements ResolverInterface
{
    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @var OrderTaxManagementInterface
     */
    private $orderTaxManagement;

    /**
     * @var ShippingTaxCalculator
     */
    private $shippingTaxCalculator;

    /**
     * @param OrderTaxManagementInterface $orderTaxManagement
     * @param TaxHelper $taxHelper
     * @param ShippingTaxCalculator $shippingTaxCalculator
     */
    public function __construct(
        OrderTaxManagementInterface $orderTaxManagement,
        TaxHelper $taxHelper,
        ShippingTaxCalculator $shippingTaxCalculator
    ) {
        $this->taxHelper = $taxHelper;
        $this->orderTaxManagement = $orderTaxManagement;
        $this->shippingTaxCalculator = $shippingTaxCalculator;
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
        if (!(($value['model'] ?? null) instanceof InvoiceInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!(($value['order'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }

        /** @var OrderInterface $orderModel */
        $orderModel = $value['order'];
        /** @var InvoiceInterface $invoiceModel */
        $invoiceModel = $value['model'];
        $currency = $orderModel->getOrderCurrencyCode();
        $baseCurrency = $orderModel->getBaseCurrencyCode();
        return [
            'base_grand_total' => ['value' => $invoiceModel->getBaseGrandTotal(), 'currency' => $baseCurrency],
            'grand_total' => ['value' =>  $invoiceModel->getGrandTotal(), 'currency' => $currency],
            'subtotal' => ['value' =>  $invoiceModel->getSubtotal(), 'currency' => $currency],
            'total_tax' => ['value' =>  $invoiceModel->getTaxAmount(), 'currency' => $currency],
            'total_shipping' => ['value' => $invoiceModel->getShippingAmount(), 'currency' => $currency],
            'discounts' => $this->getDiscountDetails($invoiceModel),
            'taxes' => $this->formatTaxes(
                $orderModel,
                $this->taxHelper->getCalculatedTaxes($invoiceModel),
            ),
            'shipping_handling' => [
                'amount_excluding_tax' => [
                    'value' => $invoiceModel->getShippingAmount() ?? 0,
                    'currency' => $currency
                ],
                'amount_including_tax' => [
                    'value' => $invoiceModel->getShippingInclTax() ?? 0,
                    'currency' => $currency
                ],
                'total_amount' => [
                    'value' => $invoiceModel->getShippingAmount() ?? 0,
                    'currency' => $currency
                ],
                'discounts' => $this->getShippingDiscountDetails($invoiceModel, $orderModel),
                'taxes' => $this->formatTaxes(
                    $orderModel,
                    $this->shippingTaxCalculator->calculateShippingTaxes($orderModel, $invoiceModel),
                )
            ]
        ];
    }

    /**
     * Return information about an applied discount on shipping
     *
     * @param InvoiceInterface $invoiceModel
     * @param OrderInterface $orderModel
     * @return array
     */
    private function getShippingDiscountDetails(InvoiceInterface $invoiceModel, OrderInterface $orderModel): array
    {
        $invoiceShippingAmount = (float)$invoiceModel->getShippingAmount();
        $orderShippingAmount = (float)$orderModel->getShippingAmount();
        $calculatedShippingRatioFromOriginal = $invoiceShippingAmount != 0 && $orderShippingAmount != 0 ?
            ($invoiceShippingAmount / $orderShippingAmount) : 0;
        $orderShippingDiscount = (float)$orderModel->getShippingDiscountAmount();
        $calculatedInvoiceShippingDiscount = $orderShippingDiscount * $calculatedShippingRatioFromOriginal;
        $shippingDiscounts = [];
        if ($calculatedInvoiceShippingDiscount != 0) {
            $shippingDiscounts[] =
                [
                    'amount' => [
                        'value' => sprintf('%.2f', abs($calculatedInvoiceShippingDiscount)),
                        'currency' => $invoiceModel->getOrderCurrencyCode()
                    ]
                ];
        }
        return $shippingDiscounts;
    }

    /**
     * Return information about an applied discount
     *
     * @param InvoiceInterface $invoice
     * @return array
     */
    private function getDiscountDetails(InvoiceInterface $invoice): array
    {
        $discounts = [];
        if (!($invoice->getDiscountDescription() === null && $invoice->getDiscountAmount() == 0)) {
            $discounts[] = [
                'label' => $invoice->getDiscountDescription() ?? __('Discount'),
                'amount' => [
                    'value' => abs((float) $invoice->getDiscountAmount()),
                    'currency' => $invoice->getOrderCurrencyCode()
                ]
            ];
        }
        return $discounts;
    }

    /**
     * Format applied taxes
     *
     * @param OrderInterface $order
     * @param array $appliedTaxes
     * @return array
     */
    private function formatTaxes(OrderInterface $order, array $appliedTaxes): array
    {
        $taxes = [];
        foreach ($appliedTaxes as $appliedTax) {
            $appliedTaxesArray = [
                'rate' => $appliedTax['percent'] ?? 0,
                'title' => $appliedTax['title'] ?? null,
                'amount' => [
                    'value' => $appliedTax['tax_amount'] ?? 0,
                    'currency' => $order->getOrderCurrencyCode()
                ]
            ];
            $taxes[] = $appliedTaxesArray;
        }
        return $taxes;
    }
}
