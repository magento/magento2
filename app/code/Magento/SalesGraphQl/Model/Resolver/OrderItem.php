<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SalesGraphQl\Model\OrderItem\DataProvider as OrderItemProvider;

/**
 * Resolve a single order item
 */
class OrderItem implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var OrderItemProvider
     */
    private $orderItemProvider;

    /**
     * @param ValueFactory $valueFactory
     * @param OrderItemProvider $orderItemProvider
     */
    public function __construct(ValueFactory $valueFactory, OrderItemProvider $orderItemProvider)
    {
        $this->valueFactory = $valueFactory;
        $this->orderItemProvider = $orderItemProvider;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $parentItem = $value['model'];

        if (!method_exists($parentItem, 'getOrderItemId')) {
            throw new LocalizedException(__('Unable to find associated order item.'));
        }

        $orderItemId = $parentItem->getOrderItemId();
        $this->orderItemProvider->addOrderItemId((int)$orderItemId);

        return $this->valueFactory->create(function () use ($parentItem) {
            $orderItem = $this->orderItemProvider->getOrderItemById((int)$parentItem->getOrderItemId());
            return empty($orderItem) ? null : $orderItem;
        });
    }
}
