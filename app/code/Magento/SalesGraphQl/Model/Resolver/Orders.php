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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

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
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @param CollectionFactoryInterface $collectionFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory,
        TimezoneInterface $timezone
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->timezone = $timezone;
    }

    /**
     * @inheritDoc
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

        $items = [];
        $orders = $this->collectionFactory->create($context->getUserId());

        /** @var Order $order */
        foreach ($orders as $order) {
            $items[] = [
                'id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'order_number' => $order->getIncrementId(),
                'created_at' => $this->getFormatDate($order->getCreatedAt()),
                'grand_total' => $order->getGrandTotal(),
                'status' => $order->getStatus(),
                'model' => $order
            ];
        }
        return ['items' => $items];
    }

    /**
     * Retrieve the timezone date
     *
     * @param string $date
     * @return string
     */
    public function getFormatDate(string $date): string
    {
        return $this->timezone->date(
            $date
        )->format('Y-m-d H:i:s');
    }
}
