<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;

/**
 * Orders data reslover
 */
class Orders implements ResolverInterface
{
    /**
     * @var CollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @param CollectionFactoryInterface $collectionFactory
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory,
        GetCustomer $getCustomer
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->getCustomer = $getCustomer;
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
        $customer = $this->getCustomer->execute($context);

        $items = [];
        $orders = $this->collectionFactory->create($customer->getId());

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($orders as $order) {
            $items[] = [
                'id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'created_at' => $order->getCreatedAt(),
                'grand_total' => $order->getGrandTotal(),
                'status' => $order->getStatus(),
            ];
        }
        return ['items' => $items];
    }
}
