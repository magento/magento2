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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Resolver for Invoice
 */
class Invoices implements ResolverInterface
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
        if (!(($value['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderInterface $orderModel */
        $orderModel = $value['model'];
        $invoices = [];
        /** @var InvoiceInterface $invoice */
        foreach ($orderModel->getInvoiceCollection() as $invoice) {
            $invoices[] = [
                'id' => base64_encode($invoice->getEntityId()),
                'number' => $invoice['increment_id'],
                'model' => $invoice,
                'order' => $orderModel
            ];
        }
        return $invoices;
    }
}
