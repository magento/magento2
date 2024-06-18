<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
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
