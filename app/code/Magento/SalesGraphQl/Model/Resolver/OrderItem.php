<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

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
        $items = [];
        /** @var OrderItemInterface $item */
        foreach ($value['items'] ?? [] as $item) {
            $items[] = [
                'parent_product_sku' => $item->getParentItem() ? $item->getParentItem()->getSku() : null,
                'product_name' => $item->getName(),
                'product_sale_price' => [
                    'currency' => 'USD',
                    'value' => '343',
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
