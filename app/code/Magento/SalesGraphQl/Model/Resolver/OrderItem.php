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
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\Data\StoreInterface;

class OrderItem implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (!isset($value['model']) && !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Order $order */
        $order = $value['model'];
        /** @var OrderItemInterface $item */
        foreach ($value['items'] ?? [] as $key => $item) {
            $items[$key] = [
                'parent_product_sku' => $item->getParentItem() ? $item->getParentItem()->getSku() : null,
                'product_name' => $item->getName(),
                'product_sale_price' => [
                    'currency' => $order->getOrderCurrencyCode(),
                    'value' => $item->getPrice(),
                ],
                'product_sku' => $item->getSku(),
                'product_url' => 'url',
                'quantity_ordered' => $item->getQtyOrdered(),
                'selected_options' => [
                    [
                        'id' => '1',
                        'value' => 4,
                    ],
                ],
                'entered_options' => [
                    [
                        'id' => '3',
                        'value' => 34,
                    ]
                ],
            ];
        }
        return $items;
    }
}
