<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Model\Order;

class GetOrderInFinalState
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct (
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param array $orderIds
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function execute(array $orderIds): \Magento\Sales\Api\Data\OrderSearchResultInterface
    {
        /** @var SearchCriteriaInterface $filter */
        $filter = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $orderIds, 'in')
            ->addFilter('state', [
                Order::STATE_COMPLETE,
                Order::STATE_CLOSED,
                Order::STATE_CANCELED
            ], 'in')
            ->create();

        return $this->orderRepository->getList($filter);
    }
}

