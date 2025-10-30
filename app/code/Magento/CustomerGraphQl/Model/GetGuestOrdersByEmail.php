<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

class GetGuestOrdersByEmail
{
    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * Retrieve customer orders collection
     *
     * @param string $email
     * @return OrderSearchResultInterface
     */
    public function execute(string $email): OrderSearchResultInterface
    {
        $this->searchCriteriaBuilder->addFilter(
            'customer_email',
            $email,
            'eq'
        )->addFilter(
            'customer_is_guest',
            1,
            'eq'
        );
        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }
}
