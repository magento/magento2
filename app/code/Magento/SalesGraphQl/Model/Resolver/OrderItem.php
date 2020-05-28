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
use Magento\SalesGraphQl\Model\SalesItem\SalesItemFactory;

class OrderItem implements ResolverInterface
{
    /**
     * @var SalesItemFactory
     */
    private $salesItemFactory;

    public function __construct(SalesItemFactory $salesItemFactory)
    {
        $this->salesItemFactory = $salesItemFactory;
    }

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
        /** @var Order $parentOrder */
        $parentOrder = $value['model'];
        /** @var OrderItemInterface $item */
        $orderItems = [];
        foreach ($parentOrder->getItems() as $key => $item) {
            $salesOrderItem = $this->salesItemFactory->create(
                $item,
                $parentOrder,
                ['quantity_ordered' => $item->getQtyOrdered()]
            );
            $orderItems[] = $salesOrderItem->convertToArray();
        }
        return $orderItems;
    }
}
