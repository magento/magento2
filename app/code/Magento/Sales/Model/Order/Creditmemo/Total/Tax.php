<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice as ResourceInvoice;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Collects credit memo taxes.
 */
class Tax extends AbstractTotal
{
    /**
     * @var ResourceInvoice
     */
    private $resourceInvoice;

    /**
     * Tax config from Tax model
     *
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @param ResourceInvoice $resourceInvoice
     * @param array $data
     * @param TaxConfig|null $taxConfig
     */
    public function __construct(ResourceInvoice $resourceInvoice, array $data = [], ?TaxConfig $taxConfig = null)
    {
        $this->resourceInvoice = $resourceInvoice;
        $this->taxConfig = $taxConfig ?: ObjectManager::getInstance()->get(TaxConfig::class);
        parent::__construct($data);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collect(Creditmemo $creditmemo)
    {
        $shippingTaxAmount = 0;
        $baseShippingTaxAmount = 0;
        $totalTax = 0;
        $baseTotalTax = 0;
        $totalDiscountTaxCompensation = 0;
        $baseTotalDiscountTaxCompensation = 0;
        $order = $creditmemo->getOrder();

        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy() || $item->getQty() <= 0) {
                continue;
            }

            $orderItemTax = (double)$orderItem->getTaxInvoiced();
            $baseOrderItemTax = (double)$orderItem->getBaseTaxInvoiced();
            $orderItemQty = (double)$orderItem->getQtyInvoiced();

            if ($orderItemQty) {
                /** Check item tax amount */
                $tax = $orderItemTax - $orderItem->getTaxRefunded();
                $baseTax = $baseOrderItemTax - $orderItem->getBaseTaxRefunded();
                $discountTaxCompensation = $orderItem->getDiscountTaxCompensationInvoiced()
                    - $orderItem->getDiscountTaxCompensationRefunded();
                $baseDiscountTaxCompensation = $orderItem->getBaseDiscountTaxCompensationInvoiced()
                    - $orderItem->getBaseDiscountTaxCompensationRefunded();
                if (!$item->isLast()) {
                    $availableQty = $orderItemQty - $orderItem->getQtyRefunded();
                    $tax = $creditmemo->roundPrice($tax / $availableQty * $item->getQty());
                    $baseTax = $creditmemo->roundPrice(($baseTax / $availableQty * $item->getQty()), 'base');
                    $discountTaxCompensation = $creditmemo->roundPrice(
                        $discountTaxCompensation / $availableQty * $item->getQty()
                    );
                    $baseDiscountTaxCompensation = $creditmemo->roundPrice(
                        $baseDiscountTaxCompensation / $availableQty * $item->getQty(),
                        'base'
                    );
                }

                $item->setTaxAmount($tax);
                $item->setBaseTaxAmount($baseTax);
                $item->setDiscountTaxCompensationAmount($discountTaxCompensation);
                $item->setBaseDiscountTaxCompensationAmount($baseDiscountTaxCompensation);

                $totalTax += $tax;
                $baseTotalTax += $baseTax;
                $totalDiscountTaxCompensation += $discountTaxCompensation;
                $baseTotalDiscountTaxCompensation += $baseDiscountTaxCompensation;
            }
        }

        $isPartialShippingRefunded = false;
        $baseOrderShippingAmount = (float)$order->getBaseShippingAmount();
        if ($invoice = $creditmemo->getInvoice()) {
            // recalculate tax amounts in case if refund shipping value was changed
            if ($baseOrderShippingAmount && $creditmemo->getBaseShippingAmount() !== null) {
                $taxFactor = $creditmemo->getBaseShippingAmount() / $baseOrderShippingAmount;
                $shippingTaxAmount = $invoice->getShippingTaxAmount() * $taxFactor;
                $baseShippingTaxAmount = $invoice->getBaseShippingTaxAmount() * $taxFactor;
                $totalDiscountTaxCompensation += $invoice->getShippingDiscountTaxCompensationAmount() * $taxFactor;
                $baseTotalDiscountTaxCompensation += $invoice->getBaseShippingDiscountTaxCompensationAmnt()
                    * $taxFactor;
                $shippingTaxAmount = $creditmemo->roundPrice($shippingTaxAmount);
                $baseShippingTaxAmount = $creditmemo->roundPrice($baseShippingTaxAmount, 'base');
                $totalDiscountTaxCompensation = $creditmemo->roundPrice($totalDiscountTaxCompensation);
                $baseTotalDiscountTaxCompensation = $creditmemo->roundPrice($baseTotalDiscountTaxCompensation, 'base');
                if ($taxFactor < 1 && $invoice->getShippingTaxAmount() > 0 ||
                    ($order->getShippingDiscountAmount() >= $order->getShippingAmount())
                ) {
                    $isPartialShippingRefunded = true;
                }
                $totalTax += $shippingTaxAmount;
                $baseTotalTax += $baseShippingTaxAmount;
            }
        } else {
            $orderShippingAmount = $order->getShippingAmount();
            $baseOrderShippingRefundedAmount = $order->getBaseShippingRefunded();
            $shippingTaxAmount = 0;
            $baseShippingTaxAmount = 0;
            $shippingDiscountTaxCompensationAmount = 0;
            $baseShippingDiscountTaxCompensationAmount = 0;
            $shippingDelta = $baseOrderShippingAmount - $baseOrderShippingRefundedAmount;

            if ($orderShippingAmount > 0 && ($shippingDelta > $creditmemo->getBaseShippingAmount() ||
                $this->isShippingIncludeTaxWithTaxAfterDiscount($order->getStoreId()))) {
                $part = $creditmemo->getShippingAmount() / $orderShippingAmount;
                $basePart = $creditmemo->getBaseShippingAmount() / $baseOrderShippingAmount;
                $shippingTaxAmount = $order->getShippingTaxAmount() * $part;
                $baseShippingTaxAmount = $order->getBaseShippingTaxAmount() * $basePart;
                $shippingDiscountTaxCompensationAmount = $order->getShippingDiscountTaxCompensationAmount() * $part;
                $baseShippingDiscountTaxCompensationAmount = $order->getBaseShippingDiscountTaxCompensationAmnt()
                    * $basePart;
                $shippingTaxAmount = $creditmemo->roundPrice($shippingTaxAmount);
                $baseShippingTaxAmount = $creditmemo->roundPrice($baseShippingTaxAmount, 'base');
                $shippingDiscountTaxCompensationAmount = $creditmemo->roundPrice(
                    $shippingDiscountTaxCompensationAmount
                );
                $baseShippingDiscountTaxCompensationAmount = $creditmemo->roundPrice(
                    $baseShippingDiscountTaxCompensationAmount,
                    'base'
                );
                if ($part < 1 && ($order->getShippingTaxAmount() > 0 ||
                        ($order->getShippingDiscountAmount() >= $order->getShippingAmount()))
                ) {
                    $isPartialShippingRefunded = true;
                }
            } elseif ($shippingDelta == $creditmemo->getBaseShippingAmount()) {
                $shippingTaxAmount = $order->getShippingTaxAmount() - $order->getShippingTaxRefunded();
                $baseShippingTaxAmount = $order->getBaseShippingTaxAmount() - $order->getBaseShippingTaxRefunded();
                $shippingDiscountTaxCompensationAmount = $order->getShippingDiscountTaxCompensationAmount()
                    - $order->getShippingDiscountTaxCompensationRefunded();
                $baseShippingDiscountTaxCompensationAmount = $order->getBaseShippingDiscountTaxCompensationAmnt()
                    - $order->getBaseShippingDiscountTaxCompensationRefunded();
            }

            $totalTax += $shippingTaxAmount;
            $baseTotalTax += $baseShippingTaxAmount;
            $totalDiscountTaxCompensation += $shippingDiscountTaxCompensationAmount;
            $baseTotalDiscountTaxCompensation += $baseShippingDiscountTaxCompensationAmount;
        }

        $allowedTax = $this->calculateAllowedTax($creditmemo);
        $allowedBaseTax = $this->calculateAllowedBaseTax($creditmemo);
        $allowedDiscountTaxCompensation = $this->calculateAllowedDiscountTaxCompensation($creditmemo);
        $allowedBaseDiscountTaxCompensation = $this->calculateAllowedBaseDiscountTaxCompensation($creditmemo);

        if ($creditmemo->isLast() && !$isPartialShippingRefunded) {
            $totalTax = $allowedTax;
            $baseTotalTax = $allowedBaseTax;
            $totalDiscountTaxCompensation = $allowedDiscountTaxCompensation;
            $baseTotalDiscountTaxCompensation = $allowedBaseDiscountTaxCompensation;
        } else {
            $totalTax = min($allowedTax, $totalTax);
            $baseTotalTax = min($allowedBaseTax, $baseTotalTax);
            $totalDiscountTaxCompensation = min($allowedDiscountTaxCompensation, $totalDiscountTaxCompensation);
            $baseTotalDiscountTaxCompensation = min(
                $allowedBaseDiscountTaxCompensation,
                $baseTotalDiscountTaxCompensation
            );
        }

        $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $totalTax);
        $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTotalTax);
        $creditmemo->setDiscountTaxCompensationAmount($totalDiscountTaxCompensation);
        $creditmemo->setBaseDiscountTaxCompensationAmount($baseTotalDiscountTaxCompensation);

        $creditmemo->setShippingTaxAmount($shippingTaxAmount);
        $creditmemo->setBaseShippingTaxAmount($baseShippingTaxAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalTax + $totalDiscountTaxCompensation);
        $creditmemo->setBaseGrandTotal(
            $creditmemo->getBaseGrandTotal() + $baseTotalTax + $baseTotalDiscountTaxCompensation
        );
        return $this;
    }

    /**
     * Checks if shipping provided incl tax, tax applied after discount, and discount applied on shipping excl tax
     *
     * @param int|null $storeId
     * @return bool
     */
    private function isShippingIncludeTaxWithTaxAfterDiscount(?int $storeId): bool
    {
        $calculationSequence = $this->taxConfig->getCalculationSequence($storeId);
        return ($calculationSequence === TaxCalculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL
            || $calculationSequence === TaxCalculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL)
            && $this->taxConfig->displaySalesShippingInclTax($storeId);
    }

    /**
     * Calculate allowed to Credit Memo tax amount
     *
     * @param Creditmemo $creditMemo
     * @return float
     */
    private function calculateAllowedTax(Creditmemo $creditMemo): float
    {
        $invoice = $creditMemo->getInvoice();
        $order = $creditMemo->getOrder();
        if ($invoice!== null) {
            $amount = $invoice->getTaxAmount()
                - $this->calculateInvoiceRefundedAmount($invoice, CreditmemoInterface::TAX_AMOUNT);
        } else {
            $amount = $order->getTaxInvoiced() - $order->getTaxRefunded();
        }

        return (float) $amount - $creditMemo->getTaxAmount();
    }

    /**
     * Calculate allowed to Credit Memo tax amount in the base currency
     *
     * @param Creditmemo $creditMemo
     * @return float
     */
    private function calculateAllowedBaseTax(Creditmemo $creditMemo): float
    {
        $invoice = $creditMemo->getInvoice();
        $order = $creditMemo->getOrder();

        if ($invoice!== null) {
            $amount = $invoice->getBaseTaxAmount()
                - $this->calculateInvoiceRefundedAmount($invoice, CreditmemoInterface::BASE_TAX_AMOUNT);
        } else {
            $amount = $order->getBaseTaxInvoiced() - $order->getBaseTaxRefunded();
        }

        return (float) $amount - $creditMemo->getBaseTaxAmount();
    }

    /**
     * Calculate allowed to Credit Memo discount tax compensation amount
     *
     * @param Creditmemo $creditMemo
     * @return float
     */
    private function calculateAllowedDiscountTaxCompensation(Creditmemo $creditMemo): float
    {
        $invoice = $creditMemo->getInvoice();
        $order = $creditMemo->getOrder();

        if ($invoice) {
            $amount = $invoice->getDiscountTaxCompensationAmount()
                + $invoice->getShippingDiscountTaxCompensationAmount()
                - $this->calculateInvoiceRefundedAmount(
                    $invoice,
                    CreditmemoInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT
                ) - $this->calculateInvoiceRefundedAmount(
                    $invoice,
                    CreditmemoInterface::SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT
                );
        } else {
            $amount = $order->getDiscountTaxCompensationInvoiced()
                + $order->getShippingDiscountTaxCompensationAmount()
                - $order->getDiscountTaxCompensationRefunded()
                - $order->getShippingDiscountTaxCompensationRefunded();
        }

        return (float) $amount
            - $creditMemo->getDiscountTaxCompensationAmount()
            - $creditMemo->getShippingDiscountTaxCompensationAmount();
    }

    /**
     * Calculate allowed to Credit Memo discount tax compensation amount in the base currency
     *
     * @param Creditmemo $creditMemo
     * @return float
     */
    private function calculateAllowedBaseDiscountTaxCompensation(Creditmemo $creditMemo): float
    {
        $invoice = $creditMemo->getInvoice();
        $order = $creditMemo->getOrder();

        if ($invoice) {
            $amount = $invoice->getBaseDiscountTaxCompensationAmount()
                + $invoice->getBaseShippingDiscountTaxCompensationAmnt()
                - $this->calculateInvoiceRefundedAmount(
                    $invoice,
                    CreditmemoInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT
                ) - $this->calculateInvoiceRefundedAmount(
                    $invoice,
                    CreditmemoInterface::BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT
                );
        } else {
            $amount = $order->getBaseDiscountTaxCompensationInvoiced()
                + $order->getBaseShippingDiscountTaxCompensationAmnt()
                - $order->getBaseDiscountTaxCompensationRefunded()
                - $order->getBaseShippingDiscountTaxCompensationRefunded();
        }

        return (float) $amount
            - $creditMemo->getBaseShippingDiscountTaxCompensationAmnt()
            - $creditMemo->getBaseDiscountTaxCompensationAmount();
    }

    /**
     * Calculate refunded amount for invoice
     *
     * @param Invoice $invoice
     * @param string $field
     * @return float
     */
    private function calculateInvoiceRefundedAmount(Invoice $invoice, string $field): float
    {
        if (empty($invoice->getId())) {
            return 0;
        }

        return $this->resourceInvoice->calculateRefundedAmount((int)$invoice->getId(), $field);
    }
}
