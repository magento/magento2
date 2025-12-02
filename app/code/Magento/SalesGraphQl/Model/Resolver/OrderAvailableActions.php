<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\GetOrderAvailableActions;

/**
 * Resolver for the available_actions in Order
 */
class OrderAvailableActions implements ResolverInterface
{
    /**
     * @param GetOrderAvailableActions $orderAvailableActionProvider
     */
    public function __construct(
        private readonly GetOrderAvailableActions $orderAvailableActionProvider
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        if (!isset($value['model']) && !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Order $order */
        $order = $value['model'];

        return $this->orderAvailableActionProvider->execute($order);
    }
}
