<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\CreditMemo;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\SalesItem\ShippingTaxCalculator;
use Magento\Tax\Api\OrderTaxManagementInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Resolve credit memo totals information
 */
class CreditMemoTotal implements ResolverInterface
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
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param OrderTaxManagementInterface $orderTaxManagement
     * @param TaxHelper $taxHelper
     * @param ShippingTaxCalculator $shippingTaxCalculator
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        OrderTaxManagementInterface $orderTaxManagement,
        TaxHelper $taxHelper,
        ShippingTaxCalculator $shippingTaxCalculator,
        PriceCurrency $priceCurrency
    ) {
        $this->taxHelper = $taxHelper;
        $this->orderTaxManagement = $orderTaxManagement;
        $this->shippingTaxCalculator = $shippingTaxCalculator;
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
        if (!(($value['model'] ?? null) instanceof CreditmemoInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!(($value['order'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }

        /** @var OrderInterface $orderModel */
        $orderModel = $value['order'];
        /** @var CreditmemoInterface $creditMemo */
        $creditMemo = $value['model'];
        $currency = $orderModel->getOrderCurrencyCode();
        $baseCurrency = $orderModel->getBaseCurrencyCode();
        return [
            'base_grand_total' => ['value' => $creditMemo->getBaseGrandTotal(), 'currency' => $baseCurrency, 'formatted' => $this->priceCurrency->format($creditMemo->getBaseGrandTotal(),false,null,null,$baseCurrency)],
            'grand_total' => ['value' =>  $creditMemo->getGrandTotal(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($creditMemo->getGrandTotal(),false,null,null,$currency)],
            'subtotal' => ['value' =>  $creditMemo->getSubtotal(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($creditMemo->getSubtotal(),false,null,null,$currency)],
            'total_tax' => ['value' =>  $creditMemo->getTaxAmount(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($creditMemo->getTaxAmount(),false,null,null,$currency)],
            'total_shipping' => ['value' => $creditMemo->getShippingAmount(), 'currency' => $currency, 'formatted' => $this->priceCurrency->format($creditMemo->getShippingAmount(),false,null,null,$currency)],
            'discounts' => $this->getDiscountDetails($creditMemo),
            'taxes' => $this->formatTaxes(
                $orderModel,
                $this->taxHelper->getCalculatedTaxes($creditMemo),
            ),
            'shipping_handling' => [
                'amount_excluding_tax' => [
                    'value' => $creditMemo->getShippingAmount() ?? 0,
                    'currency' => $currency,
                    'formatted' => $this->priceCurrency->format($creditMemo->getShippingAmount() ?? 0,false,null,null,$currency)
                ],
                'amount_including_tax' => [
                    'value' => $creditMemo->getShippingInclTax() ?? 0,
                    'currency' => $currency,
                    'formatted' => $this->priceCurrency->format($creditMemo->getShippingInclTax() ?? 0,false,null,null,$currency)
                ],
                'total_amount' => [
                    'value' => $creditMemo->getShippingAmount() ?? 0,
                    'currency' => $currency,
                    'formatted' => $this->priceCurrency->format($creditMemo->getShippingAmount() ?? 0,false,null,null,$currency)
                ],
                'discounts' => $this->getShippingDiscountDetails($creditMemo, $orderModel),
                'taxes' => $this->formatTaxes(
                    $orderModel,
                    $this->shippingTaxCalculator->calculateShippingTaxes($orderModel, $creditMemo),
                )
            ],
            'adjustment' => [
                'value' =>  abs($creditMemo->getAdjustment()),
                'currency' => $currency,
                'formatted' => $this->priceCurrency->format(abs($creditMemo->getAdjustment()),false,null,null,$currency)
            ]
        ];
    }

    /**
     * Return information about an applied discount on shipping
     *
     * @param CreditmemoInterface $creditmemoModel
     * @param OrderInterface $orderModel
     * @return array
     */
    private function getShippingDiscountDetails(CreditmemoInterface $creditmemoModel, $orderModel): array
    {
        $creditmemoShippingAmount = (float)$creditmemoModel->getShippingAmount();
        $orderShippingAmount = (float)$orderModel->getShippingAmount();
        $calculatedShippingRatio = (float)$creditmemoShippingAmount != 0 && $orderShippingAmount != 0 ?
            ($creditmemoShippingAmount / $orderShippingAmount) : 0;
        $orderShippingDiscount = (float)$orderModel->getShippingDiscountAmount();
        $calculatedCreditmemoShippingDiscount = $orderShippingDiscount * $calculatedShippingRatio;

        $shippingDiscounts = [];
        if ($calculatedCreditmemoShippingDiscount != 0) {
            $shippingDiscounts[] = [
                'amount' => [
                    'value' => sprintf('%.2f', abs($calculatedCreditmemoShippingDiscount)),
                    'currency' => $creditmemoModel->getOrderCurrencyCode(),
                    'formatted' => $this->priceCurrency->format(sprintf('%.2f', abs($calculatedCreditmemoShippingDiscount)),false,null,null,$creditmemoModel->getOrderCurrencyCode())
                ]
            ];
        }
        return $shippingDiscounts;
    }

    /**
     * Return information about an applied discount
     *
     * @param CreditmemoInterface $creditmemo
     * @return array
     */
    private function getDiscountDetails(CreditmemoInterface $creditmemo): array
    {
        $discounts = [];
        if (!($creditmemo->getDiscountDescription() === null && $creditmemo->getDiscountAmount() == 0)) {
            $discounts[] = [
                'label' => $creditmemo->getDiscountDescription() ?? __('Discount'),
                'amount' => [
                    'value' => abs($creditmemo->getDiscountAmount()),
                    'currency' => $creditmemo->getOrderCurrencyCode(),
                    'formatted' => $this->priceCurrency->format(abs($creditmemo->getDiscountAmount()),false,null,null,$creditmemo->getOrderCurrencyCode())
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
                    'currency' => $order->getOrderCurrencyCode(),
                    'formatted' => $this->priceCurrency->format($appliedTax['tax_amount'] ?? 0,false,null,null,$order->getOrderCurrencyCode())
                ]
            ];
            $taxes[] = $appliedTaxesArray;
        }
        return $taxes;
    }
}
