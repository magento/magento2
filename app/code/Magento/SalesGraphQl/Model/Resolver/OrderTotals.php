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
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

class OrderTotals implements ResolverInterface
{/**
 * @var CollectionFactoryInterface
 */
    private $collectionFactory;

    /**
     * @param CollectionFactoryInterface $collectionFactory
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        
        $orderTotals = [];
        $orders = $this->collectionFactory->create($context->getUserId());


        /** @var Order $order */
        foreach ($orders as $order) {
            $currency = $order->getOrderCurrencyCode();
            $orderTotals[] = [
                'base_grand_total' => ['value' => $order->getBaseGrandTotal(), 'currency' => $currency],
                'grand_total' => ['value' =>  $order->getGrandTotal(), 'currency' => $currency],
                'sub_total' => ['value' =>  $order->getSubtotal(), 'currency' => $currency],
                'tax' => ['value' =>  $order->getTaxAmount(), 'currency' => $currency],
                'discounts' =>['value' =>  $order->getDiscountAmount(), 'currency' => $currency],
                'shipping_handling' => ['value' => $order->getShippingAmount(), 'currency' => $currency]
            ];
        }
        return ['orderTotals' => $orderTotals];
    }
}
