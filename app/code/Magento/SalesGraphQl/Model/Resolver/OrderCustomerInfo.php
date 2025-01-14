<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\Order;

class OrderCustomerInfo implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        if (!isset($value['model']) || !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $order = $value['model'];

        return [
            "firstname" => $order->getCustomerFirstname(),
            "lastname" => $order->getCustomerLastname() ?? null,
            "middlename" => $order->getCustomerMiddlename() ?? null,
            "prefix" => $order->getCustomerPrefix() ?? null,
            "suffix" => $order->getCustomerSuffix() ?? null
        ];
    }
}
