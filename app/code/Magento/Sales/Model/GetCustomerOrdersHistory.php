<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\GetCustomerOrdersHistoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class GetCustomerOrdersHistory
 */
class GetCustomerOrdersHistory implements GetCustomerOrdersHistoryInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute(
        int $customerId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) : \Magento\Sales\Api\Data\OrderSearchResultInterface {
        $this->filterOrdersByCustomerId($customerId, $searchCriteria);

        return $this->orderRepository->getList($searchCriteria);
    }

    /**
     * Filter order list with the customer id
     *
     * @param int $customerId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return void
     */
    private function filterOrdersByCustomerId(
        int $customerId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) : void {
        $filter = $this->filterBuilder
            ->setValue($customerId)
            ->setField(OrderInterface::CUSTOMER_ID)
            ->setConditionType('eq')
            ->create();

        $groups = $searchCriteria->getFilterGroups();

        $groups[] = $this->filterGroupBuilder->create()->setFilters([$filter]);

        $searchCriteria->setFilterGroups($groups);
    }
}
