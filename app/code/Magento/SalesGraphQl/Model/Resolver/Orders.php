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
use Magento\SalesGraphQl\Model\Formatter\Converter;

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
     * @var Converter
     */
    private Converter $converter;

    /**
     * @param CollectionFactoryInterface $collectionFactory
     * @param Converter $converter
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory,
        Converter $converter = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->converter = $converter ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Converter::class);
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
                'created_at' => $this->converter->getFormatDate($order->getCreatedAt()),
                'grand_total' => $order->getGrandTotal(),
                'status' => $order->getStatus(),
                'model' => $order
            ];
        }
        return ['items' => $items];
    }
}
