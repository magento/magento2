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
use Magento\Sales\Api\Data\InvoiceInterface as Invoice;
use Magento\Sales\Model\Order;

/**
 * Resolver for Invoice total
 */
class InvoiceTotal implements ResolverInterface
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

        if (!isset($value['model']) && !($value['model'] instanceof Invoice)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!isset($value['order']) && !($value['order'] instanceof Order)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }

        /** @var Order $orderModel */
        $orderModel = $value['order'];
        /** @var Invoice $invoiceModel */
        $invoiceModel = $value['model'];
        $currency = $orderModel->getOrderCurrencyCode();
        $totals = [
            'base_grand_total' => ['value' => $invoiceModel->getBaseGrandTotal(), 'currency' => $currency],
            'grand_total' => ['value' =>  $invoiceModel->getGrandTotal(), 'currency' => $currency],
            'subtotal' => ['value' =>  $invoiceModel->getSubtotal(), 'currency' => $currency],
            'total_tax' => ['value' =>  $invoiceModel->getTaxAmount(), 'currency' => $currency],
            'taxes' => $this->getAppliedTaxes($invoiceModel, $currency),
            'total_shipping' => ['value' => $invoiceModel->getShippingAmount(), 'currency' => $currency],
            'shipping_handling' => [
                'amount_exc_tax' => ['value' => $invoiceModel->getShippingTaxAmount(), 'currency' => $currency],
                'amount_inc_tax' => ['value' => $invoiceModel->getShippingInclTax(), 'currency' => $currency],
                'total_amount' => ['value' => $invoiceModel->getBaseShippingTaxAmount(), 'currency' => $currency],
                'taxes' => $this->getAppliedTaxes($invoiceModel, $currency)
            ]
        ];
        return $totals;
    }

    /**
     * Returns taxes applied to the current invoice
     *
     * @param Invoice $invoiceModel
     * @param string $currency
     * @return array
     */
    private function getAppliedTaxes(Invoice $invoiceModel, string $currency): array
    {
        $taxes[] = [
            'rate' => $invoiceModel->getStoreToOrderRate(),
            'title' => $invoiceModel->getCustomerName(),
            'amount' => [ 'value' =>  $invoiceModel->getTaxAmount(), 'currency' => $currency
            ]
        ];
        return $taxes;
    }
}
