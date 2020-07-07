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
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Resolve credit memo totals information
 */
class CreditMemoTotal implements ResolverInterface
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

        return [
            'subtotal' => [
                'value' =>  $creditMemo->getSubtotal(),
                'currency' => $currency
            ],
            'base_grand_total' => [
                'value' => $creditMemo->getBaseGrandTotal(),
                'currency' => $currency
            ],
            'grand_total' => [
                'value' =>  $creditMemo->getGrandTotal(),
                'currency' => $currency
            ],
            'total_tax' => [
                'value' =>  $creditMemo->getTaxAmount(),
                'currency' => $currency
            ],
            'shipping_amount' => [
                'value' =>  $creditMemo->getShippingAmount(),
                'currency' => $currency
            ],
            'adjustment' => [
                'value' =>  $creditMemo->getAdjustment(),
                'currency' => $currency
            ],
        ];
    }
}
