<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\BundleOptions;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SalesGraphQl\Model\Resolver\OrderItem\DataProvider as OrderItemProvider;

/**
 * Resolve order items for Bundle Options
 */
class SelectedBundleOptionOrderItems implements ResolverInterface
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
    public function __construct(
        ValueFactory $valueFactory,
        OrderItemProvider $orderItemProvider
    ) {
        $this->valueFactory = $valueFactory;
        $this->orderItemProvider = $orderItemProvider;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['item_ids'])) {
            throw new LocalizedException(__('"item_ids" value should be specified'));
        }

        $orderItemIds = $value['item_ids'];
        foreach ($orderItemIds as $orderItemId) {
            $this->orderItemProvider->addOrderItemId((int)$orderItemId);
        }
        $itemsList = [];
        foreach ($orderItemIds as $orderItemId) {
            $itemsList[] = $this->valueFactory->create(
                function () use ($orderItemId) {
                    return $this->orderItemProvider->getOrderItemById((int)$orderItemId);
                }
            );
        }
        return $itemsList;
    }
}
