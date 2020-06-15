<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\BundleOptions;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve line items for Bundle Options
 */
class SelectedBundleOptionLineItems implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['items'])) {
            throw new LocalizedException(__('"items" value should be specified'));
        }

        $lineItems = $value['items'];
        $order = $value['order'];
        $resolvedData = [];
        foreach ($lineItems as $lineItem) {
            $resolvedData[] = [
                'product_name' => $lineItem->getName(),
                'product_sku' => $lineItem->getSku(),
                'product_sale_price' => [
                    'value' => $lineItem->getPrice(),
                    'currency' => $order->getOrderCurrency()
                ],
                'quantity_invoiced' => $lineItem->getQty(),
            ];
        }
        return $resolvedData;
    }
}
