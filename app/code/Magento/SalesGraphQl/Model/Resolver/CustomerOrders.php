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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Resolver\CustomerOrders\Query\SearchQuery;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Orders data resolver
 */
class CustomerOrders implements ResolverInterface
{
    /**
     * @var SearchQuery
     */
    private $searchQuery;

    /**
     * @param SearchQuery $orderRepository
     */
    public function __construct(
        SearchQuery $searchQuery
    ) {
        $this->searchQuery = $searchQuery;
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
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        $userId = $context->getUserId();
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $searchResult = $this->searchQuery->getResult($args, $userId, $store);

        if ($searchResult->getPageSize()) {
            $maxPages = ceil($searchResult->getTotalCount() / $searchResult->getPageSize());
        } else {
            $maxPages = 0;
        }

        $currentPage = $searchResult->getCurrentPage();
        if ($searchResult->getCurrentPage() > $maxPages && $searchResult->getTotalCount() > 0) {
            $currentPage = new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the number of pages available.',
                    [$maxPages]
                )
            );
        }

        $items = [];

        foreach (($searchResult->getItems() ?? []) as $order) {
            if (!isset($order['model']) && !($order['model'] instanceof Order)) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            /** @var Order $orderModel */
            $orderModel = $order['model'];
            $items[] = [
                'created_at' => $order['created_at'],
                'grand_total' => $order['grand_total'],
                'id' => $order['entity_id'],
                'increment_id' => $order['increment_id'],
                'number' => $order['increment_id'],
                'order_date' => $order['created_at'],
                'order_number' => $order['increment_id'],
                'status' => $orderModel->getStatusLabel(),
                'model' => $orderModel,
                'items' => $args['order_items'] ? $orderModel->getItems() : [],
                'totals' => [],
            ];
        }

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $items,
            'page_info'   => [
                'page_size'    => $searchResult->getPageSize() ?? 20,
                'current_page' => $currentPage ?? 1,
                'total_pages' => $maxPages ?? 0
            ]
        ];
    }
}
