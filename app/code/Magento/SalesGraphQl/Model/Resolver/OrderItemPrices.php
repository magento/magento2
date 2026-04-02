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
use Magento\Sales\Model\Order\Item;
use Magento\SalesGraphQl\Model\OrderItemPrices\PricesProvider;

/**
 * Resolver for OrderItemInterface.prices
 */
class OrderItemPrices implements ResolverInterface
{
    /**
     * @param PricesProvider $pricesProvider
     */
    public function __construct(
        private readonly PricesProvider $pricesProvider
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        if (!isset($value['model']) || !($value['model'] instanceof Item)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Item $orderItem */
        $orderItem = $value['model'];

        $itemPrices = $this->pricesProvider->execute($orderItem);
        $itemPrices['discounts'] = $value['discounts'];

        return $itemPrices;
    }
}
