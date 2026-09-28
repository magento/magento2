<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

class GetGuestOrdersByEmail
{
    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * Retrieve customer orders collection
     *
     * @param CustomerInterface $customer
     * @return OrderSearchResultInterface
     */
    public function execute(CustomerInterface $customer): OrderSearchResultInterface
    {
        $this->searchCriteriaBuilder
            ->addFilter('customer_email', $customer->getEmail())
            ->addFilter('customer_is_guest', 1);

        $customerAccountShareScope = (int)$this->scopeConfig->getValue(
            Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        if ($customerAccountShareScope === Share::SHARE_WEBSITE) {
            $this->searchCriteriaBuilder->addFilter(
                'store_id',
                $customer->getStoreId()
            );
        }

        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }
}
