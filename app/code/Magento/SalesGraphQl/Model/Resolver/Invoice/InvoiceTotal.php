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

/**
 * Resolver for Invoice total
 */
class InvoiceTotal implements ResolverInterface
{
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
        return [
            'base_grand_total' => ['value' => $invoiceModel->getBaseGrandTotal(), 'currency' => $currency],
            'grand_total' => ['value' =>  $invoiceModel->getGrandTotal(), 'currency' => $currency],
            'subtotal' => ['value' =>  $invoiceModel->getSubtotal(), 'currency' => $currency],
            'total_tax' => ['value' =>  $invoiceModel->getTaxAmount(), 'currency' => $currency],
            'total_shipping' => ['value' => $invoiceModel->getShippingAmount(), 'currency' => $currency],
            'shipping_handling' => [
                'amount_excluding_tax' => [
                    'value' => $invoiceModel->getShippingAmount(),
                    'currency' => $currency
                ],
                'amount_including_tax' => [
                    'value' => $invoiceModel->getShippingInclTax(),
                    'currency' => $currency
                ],
                'total_amount' => [
                    'value' => $invoiceModel->getShippingAmount(),
                    'currency' => $currency
                ],
            ]
        ];
    }
}
