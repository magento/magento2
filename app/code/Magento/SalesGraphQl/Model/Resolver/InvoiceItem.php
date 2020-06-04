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
 * Resolver for Invoice Item
 */
class InvoiceItem implements ResolverInterface
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

        /** @var Invoice $invoiceModel */
        $invoiceModel = $value['model'];
        $invoiceItems = [];
        $parentOrder = $value['order'];
        foreach ($invoiceModel->getItems() as $invoiceItem) {
            $invoiceItems[] = [
                'product_sku' => $invoiceItem->getSku(),
                'product_name' => $invoiceItem->getName(),
                'product_sale_price' => [
                    'currency' => $parentOrder->getOrderCurrencyCode(),
                    'value' => $invoiceItem->getPrice()
                ],
                'quantity_invoiced' => $invoiceItem->getQty()
            ];
        }
        return $invoiceItems;
    }
}
