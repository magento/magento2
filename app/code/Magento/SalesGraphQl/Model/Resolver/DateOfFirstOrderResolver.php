<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Data\Collection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * Resolver for CustomerOrders.date_of_first_order
 */
class DateOfFirstOrderResolver implements ResolverInterface
{
    /**
     * DateOfFirstOrderResolver Constructor
     *
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        private OrderCollectionFactory $orderCollectionFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): ?string
    {
        if ($context->getExtensionAttributes()->getIsCustomer() === false) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $storeIds = $value['store_ids'] ?: [$context->getExtensionAttributes()->getStore()->getId()];
        return $this->getDateOfFirstOrder($context->getUserId(), $storeIds);
    }

    /**
     * Get date of first order
     *
     * @param int $userId
     * @param array $storeIds
     * @return string|null
     */
    private function getDateOfFirstOrder(int $userId, array $storeIds): ?string
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', ['eq' => $userId])
            ->addFieldToFilter('store_id', ['in' => $storeIds])
            ->addOrder('created_at', Collection::SORT_ORDER_ASC);

        return $collection->getTotalCount() ? $collection->getFirstItem()->getCreatedAt() : null;
    }
}
