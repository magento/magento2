<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Api\Data\OrderInterface;
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
     * @param SearchQuery $searchQuery
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
        try {
            $searchResultDto = $this->searchQuery->getResult($args, $userId, $store);
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        $orders = [];
        foreach (($searchResultDto->getItems() ?? []) as $order) {
            if (!($order['model'] ?? null instanceof OrderInterface)) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            /** @var OrderInterface $orderModel */
            $orderModel = $order['model'];
            $orders[] = [
                'created_at' => $order['created_at'],
                'grand_total' => $order['grand_total'],
                'id' => base64_encode($order['entity_id']),
                'increment_id' => $order['increment_id'],
                'number' => $order['increment_id'],
                'order_date' => $order['created_at'],
                'order_number' => $order['increment_id'],
                'status' => $orderModel->getStatusLabel(),
                'shipping_method' => $orderModel->getShippingDescription(),
                'model' => $orderModel,
            ];
        }

        return [
            'total_count' => $searchResultDto->getTotalCount(),
            'items' => $orders,
            'page_info'   => [
                'page_size'    => $searchResultDto->getPageSize(),
                'current_page' => $searchResultDto->getCurrentPage(),
                'total_pages' => $searchResultDto->getTotalPages(),
            ]
        ];
    }
}
