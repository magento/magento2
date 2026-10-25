<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Quote\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Search and return quote by reserved order id.
 */
class GetQuoteByReservedOrderId
{
    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var CartRepositoryInterface */
    private $cartRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(SearchCriteriaBuilder $searchCriteriaBuilder, CartRepositoryInterface $cartRepository)
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Return quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return CartInterface|null
     */
    public function execute(string $reservedOrderId): ?CartInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)->create();
        $quotes = $this->cartRepository->getList($searchCriteria)->getItems();

        return array_shift($quotes);
    }
}
